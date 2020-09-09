<?php

/* For licensing terms, see /license.txt */

use ChamiloSession as Session;

/**
 * Main page for the group module.
 * This script displays the general group settings,
 * and a list of groups with buttons to view, edit...
 *
 * @author Thomas Depraetere, Hugues Peeters, Christophe Gesche: initial versions
 * @author Bert Vanderkimpen, improved self-unsubscribe for cvs
 * @author Patrick Cool, show group comment under the group name
 * @author Roan Embrechts, initial self-unsubscribe code, code cleaning, virtual course support
 * @author Bart Mollet, code cleaning, use of Display-library, list of courseAdmin-tools, use of GroupManager
 * @author Isaac Flores, code cleaning and improvements
 */
require_once __DIR__.'/../inc/global.inc.php';

$is_allowed_in_course = api_is_allowed_in_course();
$userId = api_get_user_id();
$this_section = SECTION_COURSES;
$current_course_tool = TOOL_GROUP;
$course_id = api_get_course_int_id();
$sessionId = api_get_session_id();

// Notice for unauthorized people.
api_protect_course_script(true, false, 'group');

$htmlHeadXtra[] = '<script>
$(function() {
    var i;
	for (i=0; i<$(".actions").length; i++) {
		if ($(".actions:eq("+i+")").html()=="<table border=\"0\"></table>" || $(".actions:eq("+i+")").html()=="" || $(".actions:eq("+i+")").html()==null) {
			$(".actions:eq("+i+")").hide();
		}
	}
});
 </script>';
$nameTools = get_lang('Groups management');

/*
 * Self-registration and un-registration
 */
$my_group_id = isset($_GET['group_id']) ? (int) $_GET['group_id'] : null;
$my_group = isset($_REQUEST['group']) ? Security::remove_XSS($_REQUEST['group']) : null;
$my_get_id1 = isset($_GET['id1']) ? Security::remove_XSS($_GET['id1']) : null;
$my_get_id2 = isset($_GET['id2']) ? Security::remove_XSS($_GET['id2']) : null;
$my_get_id = isset($_GET['id']) ? Security::remove_XSS($_GET['id']) : null;

$currentUrl = api_get_path(WEB_CODE_PATH).'group/group.php?'.api_get_cidreq();
$groupInfo = GroupManager::get_group_properties($my_group_id);

if (isset($_GET['action']) && $is_allowed_in_course) {
    switch ($_GET['action']) {
        case 'set_visible':
            if (api_is_allowed_to_edit()) {
                GroupManager::setVisible($my_get_id);
                Display::addFlash(Display::return_message(get_lang('Item updated')));
                header("Location: $currentUrl");
                exit;
            }

            break;
        case 'set_invisible':
            if (api_is_allowed_to_edit()) {
                GroupManager::setInvisible($my_get_id);
                Display::addFlash(Display::return_message(get_lang('Item updated')));
                header("Location: $currentUrl");
                exit;
            }

            break;
        case 'self_reg':
            if (GroupManager::is_self_registration_allowed($userId, $groupInfo)) {
                GroupManager::subscribe_users($userId, $groupInfo);
                Display::addFlash(Display::return_message(get_lang('You are now a member of this group.')));
                header("Location: $currentUrl");
                exit;
            } else {
                Display::addFlash(Display::return_message(get_lang('Error')));
                header("Location: $currentUrl");
                exit;
            }

            break;
        case 'self_unreg':
            if (GroupManager::is_self_unregistration_allowed($userId, $groupInfo)) {
                GroupManager::unsubscribe_users($userId, $groupInfo);
                Display::addFlash(Display::return_message(get_lang('You\'re now unsubscribed.')));
                header("Location: $currentUrl");
                exit;
            }

            break;
    }
}

/*
 * Group-admin functions
 */
if (api_is_allowed_to_edit(false, true)) {
    // Post-actions
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete_selected':
                if (is_array($_POST['group'])) {
                    foreach ($_POST['group'] as $myGroupId) {
                        $groupInfo = GroupManager::get_group_properties($myGroupId);
                        GroupManager::deleteGroup($groupInfo);
                    }

                    Display::addFlash(Display::return_message(get_lang('All selected groups have been deleted')));
                    header("Location: $currentUrl");
                    exit;
                }

                break;
            case 'empty_selected':
                if (is_array($_POST['group'])) {
                    foreach ($_POST['group'] as $myGroupId) {
                        $groupInfo = GroupManager::get_group_properties($myGroupId);
                        GroupManager::unsubscribe_all_users($groupInfo);
                    }

                    Display::addFlash(Display::return_message(get_lang('All selected groups are now empty')));
                    header("Location: $currentUrl");
                    exit;
                }

                break;
            case 'fill_selected':
                if (is_array($_POST['group'])) {
                    foreach ($_POST['group'] as $myGroupId) {
                        $groupInfo = GroupManager::get_group_properties($myGroupId);
                        GroupManager::fillGroupWithUsers($groupInfo);
                    }
                    Display::addFlash(Display::return_message(get_lang('All selected groups have been filled')));
                    header("Location: $currentUrl");
                    exit;
                }

                break;
        }
    }

    // Get-actions
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'swap_cat_order':
                GroupManager::swap_category_order($my_get_id1, $my_get_id2);
                Display::addFlash(Display::return_message(get_lang('The category order was changed')));
                header("Location: $currentUrl");
                exit;

                break;
            case 'delete_one':
                $groupInfo = GroupManager::get_group_properties($my_get_id);
                GroupManager::deleteGroup($groupInfo);
                Display::addFlash(Display::return_message(get_lang('Group deleted')));
                header("Location: $currentUrl");
                exit;

                break;
            case 'fill_one':
                $groupInfo = GroupManager::get_group_properties($my_get_id);
                GroupManager::fillGroupWithUsers($groupInfo);
                Display::addFlash(Display::return_message(get_lang('Groups have been filled (or completed) by users present in the \'Users\' list.')));
                header("Location: $currentUrl");
                exit;

                break;
            case 'delete_category':
                if (empty($sessionId)) {
                    GroupManager::delete_category($my_get_id);
                    Display::addFlash(
                        Display::return_message(get_lang('The category has been deleted.'))
                    );
                    header("Location: $currentUrl");
                    exit;
                }

                break;
        }
    }
}

/*	Header */
Display::display_header(get_lang('Groups'));

// Tool introduction
Display::display_introduction_section(TOOL_GROUP);

$actionsLeft = '';

if (api_is_allowed_to_edit(false, true)) {
    $actionsLeft .= '<a href="group_creation.php?'.api_get_cidreq().'">'.
        Display::return_icon('add-groups.png', get_lang('Create new group(s)'), '', ICON_SIZE_MEDIUM).'</a>';

    if ('true' === api_get_setting('allow_group_categories') && empty($sessionId)) {
        $actionsLeft .= '<a href="group_category.php?'.api_get_cidreq().'&action=add_category">'.
            Display::return_icon('new_folder.png', get_lang('Add category'), '', ICON_SIZE_MEDIUM).'</a>';
    } else {
        $actionsLeft .= '<a href="group_category.php?'.api_get_cidreq().'&id=2">'.
            Display::return_icon('settings.png', get_lang('Edit settings'), '', ICON_SIZE_MEDIUM).'</a>';
    }

    $actionsLeft .= '<a href="import.php?'.api_get_cidreq().'&action=import">'.
        Display::return_icon('import_csv.png', get_lang('Import'), '', ICON_SIZE_MEDIUM).'</a>';

    $actionsLeft .= '<a href="group_overview.php?'.api_get_cidreq().'&action=export_all&type=csv">'.
        Display::return_icon('export_csv.png', get_lang('CSV export'), '', ICON_SIZE_MEDIUM).'</a>';

    $actionsLeft .= '<a href="group_overview.php?'.api_get_cidreq().'&action=export_all&type=xls">'.
        Display::return_icon('export_excel.png', get_lang('Excel export'), '', ICON_SIZE_MEDIUM).'</a>';

    $actionsLeft .= '<a href="group_overview.php?'.api_get_cidreq().'&action=export_pdf">'.
        Display::return_icon('pdf.png', get_lang('Export to PDF'), '', ICON_SIZE_MEDIUM).'</a>';

    $actionsLeft .= '<a href="group_overview.php?'.api_get_cidreq().'">'.
        Display::return_icon('group_summary.png', get_lang('Groups overview'), '', ICON_SIZE_MEDIUM).'</a>';
}

$actionsRight = GroupManager::getSearchForm();
$toolbar = Display::toolbarAction('toolbar-groups', [$actionsLeft, $actionsRight]);
$categories = GroupManager::get_categories();
echo $toolbar;
echo UserManager::getUserSubscriptionTab(3);

/*  List all categories */
if ('true' === api_get_setting('allow_group_categories')) {
    $defaultCategory = [
        'iid' => null,
        'description' => '',
        'title' => get_lang('Default groups'),
    ];
    $categories = array_merge([$defaultCategory], $categories);

    foreach ($categories as $index => $category) {
        $categoryId = $category['iid'];
        $group_list = GroupManager::get_group_list($categoryId);
        $groupToShow = GroupManager::process_groups($group_list, $categoryId);

        if (empty($groupToShow)) {
            continue;
        }

        if (empty($categoryId) && empty($group_list)) {
            continue;
        }

        $label = Display::label(count($group_list).' '.get_lang('Groups'), 'info');
        $actions = null;
        if (api_is_allowed_to_edit(false, true) && !empty($categoryId) && empty($sessionId)) {
            // Edit
            $actions .= '<a href="group_category.php?'.api_get_cidreq().'&id='.$categoryId.'" title="'.get_lang('Edit').'">'.
                Display::return_icon('edit.png', get_lang('Edit this category'), '', ICON_SIZE_SMALL).'</a>';

            // Delete
            $actions .= Display::url(
                Display::return_icon('delete.png', get_lang('Delete'), '', ICON_SIZE_SMALL),
                'group.php?'.api_get_cidreq().'&action=delete_category&id='.$categoryId,
                [
                    'onclick' => 'javascript:if(!confirm('."'".addslashes(api_htmlentities(get_lang('Please confirm your choice'), ENT_QUOTES))."'".')) return false;',
                ]
            );
            // Move
            if (0 != $index) {
                $actions .= ' <a
                href="group.php?'.api_get_cidreq().'&action=swap_cat_order&id1='.$categoryId.'&id2='.$categories[$index - 1]['iid'].'">'.
                    Display::return_icon('up.png', '&nbsp;', '', ICON_SIZE_SMALL).'</a>';
            }
            if ($index != count($categories) - 1) {
                $actions .= ' <a
                href="group.php?'.api_get_cidreq().'&action=swap_cat_order&id1='.$categoryId.'&id2='.$categories[$index + 1]['iid'].'">'.
                    Display::return_icon('down.png', '&nbsp;', '', ICON_SIZE_SMALL).'</a>';
            }
        }

        echo Display::page_header(
            Security::remove_XSS($category['title'].' '.$label.' ').$actions,
            null,
            'h4',
            false
        );

        echo $category['description'];
        echo $groupToShow;
    }
} else {
    echo GroupManager::process_groups(GroupManager::get_group_list());
}

if (!isset($_GET['origin']) || 'learnpath' !== $_GET['origin']) {
    Display::display_footer();
}

Session::write('_gid', 0);
