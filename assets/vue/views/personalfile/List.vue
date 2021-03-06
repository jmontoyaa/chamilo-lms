<template>
  <div v-if="isAuthenticated"  class="q-card">
    <div class="p-4 flex flex-row gap-1 mb-2">
      <div class="flex flex-row gap-2" >
        <Button label="New folder" icon="fa fa-folder-plus" class="btn btn-primary" @click="openNew" />
        <Button label="Upload" icon="fa fa-file-upload" class="btn btn-primary" @click="uploadDocumentHandler()" />
        <Button label="Delete" icon="pi pi-trash" class="btn btn-danger " @click="confirmDeleteMultiple" :disabled="!selectedItems || !selectedItems.length" />
      </div>
    </div>
  </div>
  <DataTable
      class="p-datatable-sm"
      :value="items"
      v-model:selection="selectedItems"
      dataKey="iid"
      v-model:filters="filters"
      filterDisplay="menu"
      :lazy="true"
      :paginator="true"
      :rows="10"
      :totalRecords="totalItems"
      :loading="isLoading"
      @page="onPage($event)"
      @sort="sortingChanged($event)"
      paginatorTemplate="CurrentPageReport FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
      :rowsPerPageOptions="[5, 10, 20, 50]"
      responsiveLayout="scroll"
      currentPageReportTemplate="Showing {first} to {last} of {totalRecords}"
      :globalFilterFields="['resourceNode.title', 'resourceNode.updatedAt']">

    <span v-if="isCurrentTeacher">
      <Column selectionMode="multiple" style="width: 3rem" :exportable="false"></Column>
    </span>

    <Column field="resourceNode.title" :header="$t('Title')" :sortable="true">
      <template #body="slotProps">
        <div v-if="slotProps.data && slotProps.data.resourceNode && slotProps.data.resourceNode.resourceFile">
          <ResourceFileLink :resource="slotProps.data" />
        </div>
        <div v-else>
          <a
              v-if="slotProps.data"
              @click="handleClick(slotProps.data)"
              class="cursor-pointer " >
            <FontAwesomeIcon
                icon="folder"
                size="lg"
            />
            {{ slotProps.data.resourceNode.title }}
          </a>
        </div>
      </template>
    </Column>

    <Column field="resourceNode.resourceFile.size" :header="$t('Size')" :sortable="true">
      <template #body="slotProps">
        {{
          slotProps.data.resourceNode.resourceFile ? $filters.prettyBytes(slotProps.data.resourceNode.resourceFile.size) : ''
        }}
      </template>
    </Column>

    <Column field="resourceNode.updatedAt" :header="$t('Modified')" :sortable="true">
      <template #body="slotProps">
        {{$luxonDateTime.fromISO(slotProps.data.resourceNode.updatedAt).toRelative() }}
      </template>
    </Column>

    <Column :exportable="false">
      <template #body="slotProps">
        <div class="flex flex-row gap-2">
          <Button icon="fa fa-info-circle"  class="btn btn-primary " @click="showHandler(slotProps.data)" />
          <Button v-if="isAuthenticated" icon="pi pi-pencil" class="btn btn-primary p-mr-2" @click="editHandler(slotProps.data)" />
          <Button v-if="isAuthenticated" icon="pi pi-trash" class="btn btn-danger" @click="confirmDeleteItem(slotProps.data)" />
        </div>
      </template>
    </Column>

<!--    <template #paginatorLeft>-->
<!--      <Button type="button" icon="pi pi-refresh" class="p-button-text" />-->
<!--    </template>-->
<!--    <template #paginatorRight>-->
<!--      <Button type="button" icon="pi pi-cloud" class="p-button-text" />-->
<!--    </template>-->
  </DataTable>

  <Dialog v-model:visible="itemDialog" :style="{width: '450px'}" :header="$t('New folder')" :modal="true" class="p-fluid">
    <div class="p-field">
      <label for="name">{{ $t('Name') }}</label>
      <InputText
          autocomplete="off"
          id="title"
          v-model.trim="item.title"
          required="true"
          autofocus
          :class="{'p-invalid': submitted && !item.title}"
      />
      <small class="p-error" v-if="submitted && !item.title">$t('Title is required')</small>
    </div>

    <template #footer>
      <Button label="Cancel" icon="pi pi-times" class="p-button-text" @click="hideDialog"/>
      <Button label="Save" icon="pi pi-check" class="p-button-text" @click="saveItem" />
    </template>
  </Dialog>

  <Dialog v-model:visible="deleteItemDialog" :style="{width: '450px'}" header="Confirm" :modal="true">
    <div class="confirmation-content">
      <i class="pi pi-exclamation-triangle p-mr-3" style="font-size: 2rem" />
      <span v-if="item">Are you sure you want to delete <b>{{item.title}}</b>?</span>
    </div>
    <template #footer>
      <Button label="No" icon="pi pi-times" class="p-button-text" @click="deleteItemDialog = false"/>
      <Button label="Yes" icon="pi pi-check" class="p-button-text" @click="deleteItemButton" />
    </template>
  </Dialog>

  <Dialog v-model:visible="deleteMultipleDialog" :style="{width: '450px'}" header="Confirm" :modal="true">
    <div class="confirmation-content">
      <i class="pi pi-exclamation-triangle p-mr-3" style="font-size: 2rem" />
      <span v-if="item">Are you sure you want to delete the selected items?</span>
    </div>
    <template #footer>
      <Button label="No" icon="pi pi-times" class="p-button-text" @click="deleteMultipleDialog = false"/>
      <Button label="Yes" icon="pi pi-check" class="p-button-text" @click="deleteMultipleItems" />
    </template>
  </Dialog>

</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import { mapFields } from 'vuex-map-fields';
import ListMixin from '../../mixins/ListMixin';
import ActionCell from '../../components/ActionCell.vue';
//import Toolbar from '../../components/Toolbar.vue';
import ResourceFileIcon from '../../components/documents/ResourceFileIcon.vue';
import ResourceFileLink from '../../components/documents/ResourceFileLink.vue';

import { useRoute } from 'vue-router'
import DataFilter from '../../components/DataFilter';
//import DocumentsFilterForm from '../../components/personalfile/Filter';
import { ref, reactive, onMounted, computed } from 'vue';
import { useStore } from 'vuex';
import isEmpty from 'lodash/isEmpty';
import moment from "moment";

export default {
  name: 'PersonalFileList',
  servicePrefix: 'PersonalFile',
  components: {
    //8Toolbar,
    ActionCell,
    ResourceFileIcon,
    ResourceFileLink,
    //DocumentsFilterForm,
    DataFilter
  },
  mixins: [ListMixin],
  data() {
    return {
      sortBy: 'title',
      sortDesc: false,
      columnsQua: [
        {align: 'left', name: 'resourceNode.title', label: this.$i18n.t('Title'), field: 'resourceNode.title', sortable: true},
        {align: 'left', name: 'resourceNode.updatedAt', label: this.$i18n.t('Modified'), field: 'resourceNode.updatedAt', sortable: true},
        {name: 'resourceNode.resourceFile.size', label: this.$i18n.t('Size'), field: 'resourceNode.resourceFile.size', sortable: true},
        {name: 'action', label: this.$i18n.t('Actions'), field: 'action', sortable: false}
      ],
      columns: [
        { label: this.$i18n.t('Title'), field: 'title', name: 'title', sortable: true},
        { label: this.$i18n.t('Modified'), field: 'resourceNode.updatedAt', name: 'updatedAt', sortable: true},
        { label: this.$i18n.t('Size'), field: 'resourceNode.resourceFile.size', name: 'size', sortable: true},
        { label: this.$i18n.t('Actions'), name: 'action', sortable: false}
      ],
      pageOptions: [10, 20, 50, this.$i18n.t('All')],
      selected: [],
      isBusy: false,
      options: [],
      selectedItems: [],
      // prime vue
      itemDialog: false,
      deleteItemDialog: false,
      deleteMultipleDialog: false,
      item: {},
      filters: {},
      submitted: false,
    };
  },
  created() {
    console.log('CREATED');
    let resourceNodeId = this.currentUser.resourceNode['id'];
    if (isEmpty(this.$route.params.node)) {
      this.$route.params.node = resourceNodeId;
    }
    //this.item.parentResourceNodeId = this.$route.params.node;
    this.filters['resourceNode.parent'] = resourceNodeId;
  },
  mounted() {
    const route = useRoute()
    /*let nodeId = route.params['node'];
    if (!isEmpty(nodeId)) {
      this.findResourceNode('/api/resource_nodes/' + nodeId);
    }*/
    console.log(this.options);
    this.onUpdateOptions(this.options);
  },
  computed: {
    // From crud.js list function
    ...mapGetters('resourcenode', {
      resourceNode: 'getResourceNode'
    }),
    ...mapGetters({
      'isAuthenticated': 'security/isAuthenticated',
      'isAdmin': 'security/isAdmin',
      'currentUser': 'security/getUser',
    }),

    ...mapGetters('personalfile', {
      items: 'list',
    }),

    //...getters

    // From ListMixin
    ...mapFields('personalfile', {
      deletedItem: 'deleted',
      error: 'error',
      isLoading: 'isLoading',
      resetList: 'resetList',
      totalItems: 'totalItems',
      view: 'view'
    }),
  },
  methods: {
    // prime
    onPage(event) {
      console.log(event);
      console.log(event.page);
      console.log(event.sortField);
      console.log(event.sortOrder);

      this.options.itemsPerPage = event.rows;
      this.options.page = event.page + 1;
      this.options.sortBy = event.sortField;
      this.options.sortDesc = event.sortOrder === -1;

      this.onUpdateOptions(this.options);
    },
    sortingChanged(event) {
      console.log('sortingChanged');
      console.log(event);
      this.options.sortBy = event.sortField;
      this.options.sortDesc = event.sortOrder === -1;

      this.onUpdateOptions(this.options);
      // ctx.sortBy   ==> Field key for sorting by (or null for no sorting)
      // ctx.sortDesc ==> true if sorting descending, false otherwise
    },

    openNew() {
      this.item = {};
      this.submitted = false;
      this.itemDialog = true;
    },
    hideDialog() {
      this.itemDialog = false;
      this.submitted = false;
    },
    saveItem() {
      this.submitted = true;

      if (this.item.title.trim()) {
        if (this.item.id) {
        } else {
          let resourceNodeId = this.currentUser.resourceNode['id'];
          if (!isEmpty(this.$route.params.node)) {
            resourceNodeId = this.$route.params.node;
          }

          this.item.parentResourceNodeId = resourceNodeId;
          this.item.resourceLinkList = JSON.stringify([{
            gid: 0,
            sid: 0,
            c_id: 0,
            visibility: 2, // visible by default
          }]);

          this.create(this.item);
          this.showMessage('Saved');
        }

        this.itemDialog = false;
        this.item = {};
      }
    },
    editItem(item) {
      this.item = {...item};
      this.itemDialog = true;
    },
    confirmDeleteItem(item) {
      this.item = item;
      this.deleteItemDialog = true;
    },
    confirmDeleteMultiple() {
      this.deleteMultipleDialog = true;
    },
    deleteMultipleItems() {
      console.log('deleteMultipleItems');
      console.log(this.selectedItems);
      this.deleteMultipleAction(this.selectedItems);
      this.onRequest({
        pagination: this.pagination,
      });
      this.deleteMultipleDialog = false;
      this.selectedItems = null;
      //this.$toast.add({severity:'success', summary: 'Successful', detail: 'Products Deleted', life: 3000});*/
    },
    deleteItemButton() {
      console.log('deleteItem');
      this.deleteItem(this.item);
      //this.items = this.items.filter(val => val.iid !== this.item.iid);
      this.deleteItemDialog = false;
      this.item = {};
      this.onUpdateOptions(this.options);
    },
    onRowSelected(items) {
      this.selected = items
    },
    selectAllRows() {
      this.$refs.selectableTable.selectAllRows()
    },
    clearSelected() {
      this.$refs.selectableTable.clearSelected()
    },
    async deleteSelected() {
      console.log('deleteSelected');
      /*for (let i = 0; i < this.selected.length; i++) {
        let item = this.selected[i];
        //this.deleteHandler(item);
        this.deleteItem(item);
      }*/

      this.deleteMultipleAction(this.selected);
      this.onRequest({
        pagination: this.pagination,
      });
      console.log('end -- deleteSelected');
    },
    //...actions,
    // From ListMixin
    ...mapActions('personalfile', {
      getPage: 'fetchAll',
      create: 'create',
      deleteItem: 'del',
      deleteMultipleAction: 'delMultiple'
    }),
    ...mapActions('resourcenode', {
      findResourceNode: 'findResourceNode',
    }),
  }
};
</script>
