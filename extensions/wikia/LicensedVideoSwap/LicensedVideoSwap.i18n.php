<?php

/**
* Internationalisation file for the LicensedVideoSwap extension.
*
* @addtogroup Languages
*/

$messages = array();

$messages['en'] = array(
	'lvs-page-title' => 'Licensed Video Swap',
	'lvs-callout-header' => "We've found matches for videos on your wiki in Wikia Video. <br> Replacing your videos with videos from Wikia Video is a good idea because:",
	'lvs-callout-reason-licensed' => "Wikia Videos are '''licensed''' for our communities for use on your wikis",
	'lvs-callout-reason-quality' => "Wikia Videos are high '''quality'''",
	'lvs-callout-reason-collaborative' => "Wikia Videos are '''collaborative''' and can be '''used across multiple wikis'''",
	'lvs-callout-reason-more' => 'and more... we will be adding more features and ways to easily use and manage Wikia Videos. Stay tuned!',
	'lvs-instructions-header' => 'How to use this page',
	'lvs-instructions' => "Many of the videos you embed on your wikis become unavailable when they are removed or taken down for copyright violations. That's why Wikia has licensed thousands of videos for use on your wikis from several content partners. This Special page is an easy way for you to see if we have a licensed copy of the same or similar videos on your wikis. Please note that often the exact same video may have a different video thumbnail so it's best to review the videos before you make a decision. Happy swapping!",
	'lvs-button-keep' => 'Keep',
	'lvs-button-swap' => 'Swap',
	'lvs-more-suggestions' => '$1 more {{PLURAL:$1|suggestion|suggestions}}',
	'lvs-best-match-label' => 'Best Match from Wikia Video',
	'lvs-undo-swap' => 'Undo',
	'lvs-undo-keep' => 'Undo',
	'lvs-swap-video-success' => 'You have successfully replaced your video with a Wikia Video. You can check the file page $1. $2',
	'lvs-file-link-text' => 'here',
	'lvs-keep-video-success' => 'You have chosen to keep your current video. The video will be removed from this list. $1',
	'lvs-restore-video-success' => 'You have restored the video to this list.',
	'lvs-error-permission' => 'You cannot swap this video.',
	'lvs-error-invalid-page-status' => 'You cannot restore this video.',
	'lvs-posted-in-label' => 'Current video posted in ',
	'lvs-posted-in-label-none' => 'Current video is not posted in any articles',
	'lvs-posted-in-more' => 'more',
	'lvs-confirm-swap-title' => 'Swap Video',
	'lvs-confirm-swap-message-same-title' => "You are about to swap out '''$1''' with a Wikia Video on your wiki. This will replace all instances of the video, including any videos embedded in articles, and the original video will be deleted. Any changes can be reversed later from History on this Special page. Do you want to continue?",
	'lvs-confirm-swap-message-different-title' => "You are about to swap out '''$1''' with '''$2''' on your wiki. This will replace all instances of the video, including any videos embedded in articles. The original video will be deleted and the file page will be redirected to the new video file page. Any changes can be reversed later from History on this Special page. Do you want to continue?",
	'lvs-confirm-keep-title' => 'Keep Video',
	'lvs-confirm-keep-message' => 'You have chosen not to replace your current video with a licensed Wikia Video. Do you want to continue?',
	'lvs-confirm-undo-swap-title' => 'Confirm Undo',
	'lvs-confirm-undo-swap-message' => 'Are you sure you want to restore the original video?',
	'lvs-confirm-undo-keep-title' => 'Confirm Undo',
	'lvs-confirm-undo-keep-message' => 'Are you sure you want to add this video back into the list?',
	'lvs-no-matching-videos' => 'There are currently no premium videos related to this video',
	'lvs-log-swap' => 'Swapped video from [[{{ns:File}}:$1]] to [[{{ns:File}}:$2]]',
	'lvs-log-restore' => 'Restored swapped video ([[{{ns:File}}:$1]])',
	'lvs-log-removed-redirected-link' => 'Removed redirected link',
	'lvs-zero-state' => "There are no unlicensed videos to review or we haven't found any matches for the unlicensed videos on this wiki.",
);

$messages['qqq'] = array(
	'lvs-page-title' => 'This is the page header/title (h1 tag) that is displayed at the top of the page.  This section is temporary and will go away after a certain number of views.',
	'lvs-callout-header' => 'This is some header text that encourages the user to replace unlicensed videos with videos licensed for use on Wikia.  This section is temporary and will go away after a certain number of views. There\'s an optional "<br />" tag between the two sentences for purposes of making the header look nicer.',
	'lvs-callout-reason-licensed' => 'This is a bullet point that appears below lvs-callout-header. It explains that Wikia videos are licensed for use on Wikia. This section is temporary and will go away after a certain number of views.',
	'lvs-callout-reason-quality' => 'This is a bullet point that appears below lvs-callout-header.  This section is temporary and will go away after a certain number of views.',
	'lvs-callout-reason-collaborative' => 'This is a bullet point that appears below lvs-callout-header.  This section is temporary and will go away after a certain number of views.',
	'lvs-callout-reason-more' => 'This is a bullet point that appears below lvs-callout-header.  This section is temporary and will go away after a certain number of views.',
	'lvs-instructions-header' => 'This is the title of the section on how to use this page.',
	'lvs-instructions' => 'This is the text at the top of the Licensed Video Swap special page that explains to the user what this page is all about. The idea is that users can exchange unlicensed videos for videos licensed for use on Wikia.',
	'lvs-button-keep' => 'This is the text that appears on a button that, when clicked, will keep the non-licensed video as opposed to swapping it out for a licensed video.',
	'lvs-button-swap' => 'This is the text that appears on a button that, when clicked, will swap out a non-licensed video for a licensed video suggested from the wikia video library.',
	'lvs-more-suggestions' => 'This text will appear below a video that is a suggestion for a licensed version of a video that already exists on the wiki.  When clicked, this link will reveal more licensed possible replacements for the non-licensed video.',
	'lvs-best-match-label' => 'This text appears above the licensed video that is considered the best match for replacing a non-licensed video.',
	'lvs-undo-swap' => 'This text appears after swapping out the video to undo the swapping video.',
	'lvs-undo-keep' => 'This text appears after keeping the video to undo the keeping video.',
	'lvs-swap-video-success' => 'This text appears after swapping out the video.
* $1 is a link to the file page
* $2 is a link to reverse the replacement',
	'lvs-file-link-text' => 'This text is for file page link',
	'lvs-keep-video-success' => 'This text appears after keeping the video.
* $1 is the title of the video
* $2 is a link to restore the video to the Special page again',
	'lvs-restore-video-success' => 'This text appears after restoring the video to the list.',
	'lvs-error-permission' => 'This text appears if user does not have permission to swap the video.',
	'lvs-error-invalid-page-status' => 'This text appears if the file is in invalid status',
	'lvs-posted-in-label' => 'This is the label text that appears before a list of titles in which the video is posted.  Due to design constraints, it comes before the list, so if, when translated, it would otherwise come after the list, please do your best to adjust accordingly.  ex: "Current video posted in: title1, title2, title3."  It is up to you if you want to include a colon at the end.',
	'lvs-posted-in-more' => 'This is the text that is shown after a truncated list of titles in which a video is posted.  When hovered, a full list appears.  When clicked, the user is taken to a page where the full list is displayed.',
	'lvs-confirm-swap-title' => 'This is the heading that is displayed in the confirm swap modal.',
	'lvs-confirm-swap-message-same-title' => 'This message is show in a modal when a user clicks a button to swap out an un-licensed video for a licensed video. It is a coonfirmation message.',
	'lvs-confirm-swap-message-different-title' => '',
	'lvs-confirm-keep-title' => 'This is the heading that is displayed in the confirm keep modal.',
	'lvs-confirm-keep-message' => 'This message is show in a modal when a user clicks a button to keep an un-licensed video as opposed to swapping it out for a licensed video. It is a coonfirmation message.',
	'lvs-confirm-undo-swap-title' => 'This is the heading that is displayed in the confirm undo swap modal',
	'lvs-confirm-undo-swap-message' => 'This message is show in a modal to confirm that a user wants to revert a video swap, i.e. the non-premium video they had originally replaced with a premium video will be restored.',
	'lvs-confirm-undo-keep-title' => 'This is the heading that is displayed in the confirm undo keep modal',
	'lvs-confirm-undo-keep-message' => 'This message is show in a modal to confirm that a user wants to un-keep a video, i.e. they chose to keep a non-premium but then decided to add it back into the list of videos with suggestions for swapping.',
	'lvs-no-matching-videos' => 'Message shown when no video can be found that matches the title of the youtube video we intend to swap',
	'lvs-log-swap' => 'log message shown in Special:RecentChanges for swapping video.',
	'lvs-log-restore' => 'log message shown in Special:RecentChanges for restoring swapped video.',
	'lvs-zero-state' => 'This message is displayed if there are no unlicenced videos to review on the licensed video swap page.',
);