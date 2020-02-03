<?php

/* For licensing terms, see /license.txt */

use Chamilo\CoreBundle\Framework\Container;
use Chamilo\CourseBundle\Entity\CForumForum;
use Chamilo\CourseBundle\Entity\CForumPost;
use Chamilo\CourseBundle\Entity\CForumThread;

/**
 * These files are a complete rework of the forum. The database structure is
 * based on phpBB but all the code is rewritten. A lot of new functionalities
 * are added:
 * - forum categories and forums can be sorted up or down, locked or made invisible
 * - consistent and integrated forum administration
 * - forum options:     are students allowed to edit their post?
 *                      moderation of posts (approval)
 *                      reply only forums (students cannot create new threads)
 *                      multiple forums per group
 * - sticky messages
 * - new view option: nested view
 * - quoting a message.
 *
 * @Author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @Copyright Ghent University
 * @Copyright Patrick Cool
 */
require_once __DIR__.'/../inc/global.inc.php';

// The section (tabs).
$this_section = SECTION_COURSES;

// Notification for unauthorized people.
api_protect_course_script(true);

$nameTools = get_lang('Forums');

// Unset the formElements in session before the includes function works
unset($_SESSION['formelements']);

require_once 'forumfunction.inc.php';

// Are we in a lp ?
$origin = api_get_origin();

/* MAIN DISPLAY SECTION */

/* Retrieving forum and forum category information */

// We are getting all the information about the current forum and forum category.
// Note pcool: I tried to use only one sql statement (and function) for this,
// but the problem is that the visibility of the forum AND forum category are stored in the item_property table.
$forumId = isset($_GET['forum']) ? (int) $_GET['forum'] : 0;
$userId = api_get_user_id();

$repo = Container::getForumRepository();
/** @var CForumForum $forum */
$forum = $repo->find($forumId);

$repoThread = Container::getForumThreadRepository();
/** @var CForumThread $thread */
$thread = $repoThread->find($_GET['thread']);

$category = $forum->getForumCategory();

$postRepo = Container::getForumPostRepository();
/** @var CForumPost $post */
$post = $postRepo->find($_GET['post']);

$courseEntity = api_get_course_entity();
$sessionEntity = api_get_session_entity();

$forumIsVisible = $forum->isVisible($courseEntity, $sessionEntity);
$categoryIsVisible = $category->isVisible($courseEntity, $sessionEntity);

if (empty($post)) {
    api_not_allowed(true);
}

api_block_course_item_locked_by_gradebook($_GET['thread'], LINK_FORUM_THREAD);

$isEditable = postIsEditableByStudent($forum, $post);
if (!$isEditable) {
    api_not_allowed(true);
}

if (api_is_in_gradebook()) {
    $interbreadcrumb[] = [
        'url' => Category::getUrl(),
        'name' => get_lang('Assessments'),
    ];
}

$group_properties = GroupManager::get_group_properties(api_get_group_id());
if ('group' === $origin) {
    $_clean['toolgroup'] = api_get_group_id();
    $interbreadcrumb[] = [
        'url' => api_get_path(WEB_CODE_PATH).'group/group.php?'.api_get_cidreq(),
        'name' => get_lang('Groups'),
    ];
    $interbreadcrumb[] = [
        'url' => api_get_path(WEB_CODE_PATH).'group/group_space.php?'.api_get_cidreq(),
        'name' => get_lang('Group area').' '.$group_properties['name'],
    ];
    $interbreadcrumb[] = [
        'url' => api_get_path(WEB_CODE_PATH).'forum/viewforum.php?'.api_get_cidreq().'&forum='.$forumId,
        'name' => prepare4display($forum->getForumTitle()),
    ];
    $interbreadcrumb[] = ['url' => 'javascript: void (0);', 'name' => get_lang('Edit a post')];
} else {
    $interbreadcrumb[] = [
        'url' => api_get_path(WEB_CODE_PATH).'forum/index.php?'.api_get_cidreq(),
        'name' => $nameTools,
    ];
    $interbreadcrumb[] = [
        'url' => api_get_path(WEB_CODE_PATH).'forum/viewforumcategory.php?forumcategory='.$category->getIid().'&'.api_get_cidreq(),
        'name' => prepare4display($category->getCatTitle()),
    ];
    $interbreadcrumb[] = [
        'url' => api_get_path(WEB_CODE_PATH).'forum/viewforum.php?forum='.$forumId.'&'.api_get_cidreq(),
        'name' => prepare4display($forum->getForumTitle()),
    ];
    $interbreadcrumb[] = [
        'url' => api_get_path(WEB_CODE_PATH).'forum/viewthread.php?'.api_get_cidreq().'&forum='.$forumId.'&thread='.(int) ($_GET['thread']),
        'name' => prepare4display($thread->getThreadTitle()),
    ];
    $interbreadcrumb[] = ['url' => 'javascript: void (0);', 'name' => get_lang('Edit a post')];
}

$table_link = Database::get_main_table(TABLE_MAIN_GRADEBOOK_LINK);

/* Header */
$htmlHeadXtra[] = <<<JS
    <script>
    $(function() {
        $('#reply-add-attachment').on('click', function(e) {
            e.preventDefault();
            var newInputFile = $('<input>', {
                type: 'file',
                name: 'user_upload[]'
            });
            $('[name="user_upload[]"]').parent().append(newInputFile);
        });
    });
    </script>
JS;

/* Is the user allowed here? */

// The user is not allowed here if
// 1. the forum category, forum or thread is invisible (visibility==0)
// 2. the forum category, forum or thread is locked (locked <>0)
// 3. if anonymous posts are not allowed
// 4. if editing of replies is not allowed
// The only exception is the course manager
// I have split this is several pieces for clarity.
if (!api_is_allowed_to_edit(null, true) &&
    (
        (false === $categoryIsVisible) ||
        false === $forumIsVisible
    )
) {
    api_not_allowed(true);
}

if (!api_is_allowed_to_edit(null, true) &&
    (
        ($category->getLocked()) ||
        0 != $forum->getLocked() ||
        0 != $thread->getLocked()
    )
) {
    api_not_allowed(true);
}

if (!$userId && 0 == $forum->getAllowAnonymous()) {
    api_not_allowed(true);
}

$group_id = api_get_group_id();

if (!api_is_allowed_to_edit(null, true) &&
    0 == $forum->getAllowEdit() &&
    !GroupManager::is_tutor_of_group(api_get_user_id(), $group_properties)
) {
    api_not_allowed(true);
}

if ('learnpath' === $origin) {
    Display::display_reduced_header();
} else {
    Display::display_header();
}

// Action links
if ('learnpath' !== $origin) {
    echo '<div class="actions">';
    echo '<span style="float:right;">'.search_link().'</span>';
    if ('group' === $origin) {
        echo '<a href="../group/group_space.php?'.api_get_cidreq().'">'.
            Display::return_icon(
                'back.png',
                get_lang('Back to').' '.get_lang('Groups'),
                '',
                ICON_SIZE_MEDIUM
            ).
            '</a>';
    } else {
        echo '<a href="index.php?'.api_get_cidreq().'">'.
            Display::return_icon(
                'back.png',
                get_lang('Back toForumOverview'),
                '',
                ICON_SIZE_MEDIUM
            ).
            '</a>';
    }
    echo '<a href="viewforum.php?forum='.$forumId.'&'.api_get_cidreq().'">'.
        Display::return_icon(
            'forum.png',
            get_lang('Back toForum'),
            '',
            ICON_SIZE_MEDIUM
        ).
        '</a>';
    echo '</div>';
}

/* Display Forum Category and the Forum information */
/*New display forum div*/
echo '<div class="forum_title">';
echo '<h1>';
echo Display::url(
    prepare4display($forum->getForumTitle()),
    'viewforum.php?'.api_get_cidreq().'&'.http_build_query([
        'origin' => $origin,
        'forum' => $forum->getIid(),
    ]),
    ['class' => false === $forumIsVisible ? 'text-muted' : null]
);
echo '</h1>';
echo '<p class="forum_description">'.prepare4display($forum->getForumComment()).'</p>';
echo '</div>';
/* End new display forum */

// Set forum attachment data into $_SESSION
getAttachedFiles(
    $forum->getIid(),
    $thread->getIid(),
    $post->getIid()
);

show_edit_post_form(
    $post,
    $thread,
    $forum,
    isset($_SESSION['formelements']) ? $_SESSION['formelements'] : ''
);

// Footer
if (isset($origin) && 'learnpath' === $origin) {
    Display::display_reduced_footer();
} else {
    Display::display_footer();
}
