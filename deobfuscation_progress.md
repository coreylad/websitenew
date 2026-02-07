### File: details.php

| Obfuscated Name | Cleartext Name         |
|-----------------|-----------------------|
| $id             | $torrentId            |
| $tab            | $tabSection           |
| $defaulttemplate| $defaultTemplate      |
| $Imagedir       | $imageDir             |
| $dimagedir      | $templateImageDir     |
| $query          | $torrentQuery / $torrentResult |
| $hash           | $memcacheHash         |

All usages and definitions updated. Legacy/obfuscation-related comments removed.
### File: direct-download.php

| Obfuscated Name | Cleartext Name         |
|-----------------|-----------------------|
| $id             | $torrentId            |
| $res            | $torrentResult        |
| $row            | $torrentRow           |
| $gt             | $thanksQuery          |
| $downperm       | $userDownloadPermission |
| $query          | $userPermissionQuery  |

All usages and definitions updated. Legacy/obfuscation-related comments removed.
### File: comment.php

| Obfuscated Name | Cleartext Name      |
|-----------------|--------------------|
| $rt             | $quickCommentAnchor |
| $arr            | $torrentInfo       |

All usages and definitions updated. Legacy/obfuscation-related comments removed.
### File: browse.php

| Obfuscated Name | Cleartext Name         |
|-----------------|-----------------------|
| $sc             | $categorySub          |
| $c              | $categoryMain         |
| $t              | $torrentRow           |
| $searcincategories | $searchInCategories |
| $scdesc         | $categorySubDescription |
| $cname          | $categoryMainName     |
| $cdesc          | $categoryMainDescription |
| $SEOLinkC       | $seoLinkCategory      |
| $TotalTorrents  | $totalTorrents        |

All usages and definitions updated. Legacy/obfuscation-related comments removed.
### File: ts_blog.php

| Obfuscated Name | Cleartext Name |
|-----------------|----------------|
| $do             | $blogAction    |
| $blog_error     | $blogErrors    |
| $blog_image_path| $blogImagePath |
| $defaulttemplate| $defaultTemplate |
| $dimagedir      | $templateImageDir |
| $prvp           | $previewHtml   |
| $TotalBlogs     | $totalBlogs    |
| $AllowedMax     | $allowedMaxBlogs |
| $uid            | $userId        |
| $title          | $blogTitle     |
| $desc           | $blogDescription |
| $date           | $blogDate      |
| $BID            | $blogId        |

All usages and definitions updated. Legacy/obfuscation-related comments removed.
### File: download.php

| Obfuscated Name | Cleartext Name |
|-----------------|----------------|
| $id             | $torrentId     |
| $res            | $torrentResult |
| $row            | $torrentRow    |
| $query          | $userPermissionQuery |
| $downperm       | $userDownloadPermission |
| $ratio          | $userRatio     |
| $has            | $hasCompleted |
| $percentage     | $downloadPercentage |
| $warning_message| $warningMessage |
| $external       | $isExternalTorrent |
| $fn             | $torrentFilePath |
| $gt             | $thanksQuery   |
| $Data           | $torrentData   |
| $Torrent        | $torrentObject |

All usages and definitions updated. Legacy/obfuscation-related comments removed.
### File: confirmemail.php

| Obfuscated Name | Cleartext Name |
|-----------------|----------------|
| $id             | $userId        |
| $md5            | $userHash      |
| $email          | $userEmail     |
| $res            | $userValidationResult |
| $row            | $userValidationRow |

All usages and definitions updated. Legacy/obfuscation-related comments removed.
# tsf_forums/poll.php
| Obfuscated/Short Name | Descriptive Name |
|----------------------|------------------|
| $do                  | $pollAction      |
| $query               | $threadQuery     |
| $thread              | $threadRow       |
| $forummoderator      | $forumModerator  |
| $error               | $pollError       |
| $optionscount        | $optionsCount    |
| $optionsarray        | $optionsArray    |
| $votesarray          | $votesArray      |
| $optionid            | $optionId        |
| $optiontext          | $optionText      |
| $showpolloptions     | $showPollOptionsHtml |
| $i                   | $optionIndex     |
| $numberoptions       | $numberOptions   |
# shoutcast/dj.php
| Obfuscated/Short Name | Descriptive Name |
|----------------------|------------------|
| $i                   | $dayIndex        |
| $ad                  | $activeDay       |
| $DJ                  | $djEditRow       |
| $djData              | $djDataRow       |
| $availabledays       | $availableDaysList|
| $days                | $daysCheckboxesHtml|
| $Query               | $Query           |
| $djResult            | $djResult        |
# logout.php
| Obfuscated/Short Name | Descriptive Name |
|----------------------|------------------|
| $USERIPADDRESS       | $userIpHash      |
| TSDetectUserIP       | getUserIpAddress |
| $ip                  | $detectedIp      |
| $ips                 | $ipList          |
| $i                   | foreach $ipAddress|
# ts_albums.php
| Obfuscated/Short Name | Descriptive Name |
|----------------------|------------------|
| $do                  | $albumAction     |
| $Query               | $editCommentQuery, $deleteCommentQuery, etc. |
| $Result              | $deleteCommentRow, $userOptionsRow, etc. |
| $Album               | $albumRow        |
| $OrjFilename         | $originalFileName|
| $foo                 | $imageUploadHandler |
| $FileName            | $cleanFileName   |
| $FullPath            | $fullImagePath   |
| $NewName             | $newImageName    |
| $UpladableImages     | $uploadableImagesCount |
| $ImageInputFields    | $imageInputFieldsHtml |
| $i                   | $imageIndex, $imageInputIndex |
| $uploadedimageids    | $uploadedImageIds|
| $totaluploded        | $totalUploaded   |
| $AlbumImages         | $albumImagesArray|
| $UploadedImage       | $uploadedImage   |
| $prvp                | $previewHtml     |
| $Comment             | $commentRow      |
| $message             | $commentMessage  |
# ts_social_groups.php (final block)
| Obfuscated/Short Name | Descriptive Name |
|----------------------|------------------|
| $CreateGroupButton   | $createGroupButton |
| $Query               | $allGroupsQuery   |
| $SG                  | $groupRow         |
| $str                 | $pageHtml         |
| $Images              | $images           |
| $JoinButton          | $joinButton       |
| $DeleteButton        | $deleteButton     |
| $EditButton          | $editButton       |
| $Name                | $groupName        |
| $Created             | $createdDate      |
| $Members             | $membersCount     |
| $Messages            | $messagesCount    |
| $Lastpost            | $lastPost         |
| $Memberof            | $memberOfGroups   |
### staff.php

| Obfuscated Name | Cleartext Name |
|-----------------|----------------|
| $dt             | $userTimeoutDelta |
| $groups         | $staffGroups |
| $query          | $staffGroupsQuery / $staffUsersQuery |
| $group          | $staffGroup |
| $groups_in      | $staffGroupsIn |
| $arr            | $staffUser |
| $userid         | $userId |
| $last_access    | $lastAccess |
| $staff_table    | $staffTable |
### port_check.php

| Obfuscated Name | Cleartext Name |
|-----------------|----------------|
| checkconnect    | checkPortConnection |
| $ip             | $ipAddress |
| $port           | $portNumber |
| $sockres        | $socketResource |
| $errno          | $errorNumber |
| $errstr         | $errorString |
| $timeout        | $connectionTimeout |
| $result         | $connectionResult |
### shoutcast/dj.php

| Obfuscated Name | Cleartext Name |
|-----------------|----------------|
| $_GET["do"]     | $_GET["action"] |
| $_GET["id"]     | $_GET["djId"] |
| $Query          | $djQuery / $djListQuery |
| $Result         | $djResult |
| $query          | $djRequestQuery / $staffQuery |
| $id             | $djInsertId |
| $activedays     | $activeDays |
| $activetime     | $activeTime |
| $selectedadays  | $selectedDays |
| $days           | $daysCheckboxes |
| $List           | $djList |
| $activedjlist   | $activeDjList |
### shoutcast/php/song.name.php

| Obfuscated Name | Cleartext Name |
|-----------------|----------------|
| $LPS            | $lastPlayedSongs |
| $s              | $songTitle |
### viewsnatches.php

| Obfuscated Name | Cleartext Name |
|-----------------|----------------|
| $id             | $torrentId |
| $userid         | $userId |
| $res3           | $snatchCountResult / $torrentInfoResult |
| $row            | $snatchCountRow |
| $count          | $snatchCount |
| $torrentsperpage| $torrentsPerPage |
| $arr3           | $torrentInfo |
| $type           | $sortType |
| $orderby        | $orderBy |
| $typelink       | $typeLink |
| $orderlink      | $orderLink |
| $quicklink      | $quickLink |
| $pagertop       | $pagerTop |
| $pagerbottom    | $pagerBottom |
### viewrequests.php

| Obfuscated Name | Cleartext Name |
|-----------------|----------------|
| $do             | $requestAction |
| $rid            | $requestId |
| check_rid       | checkRequestId |
| check_rid_permission | checkRequestIdPermission |
| unesc           | unescapeString |
### viewnfo.php

| Obfuscated Name | Cleartext Name |
|-----------------|----------------|
| $id             | $nfoId |
| $Error          | $hasError |
| $NFO            | $nfoData |
| $red            | $colorRed |
| $green          | $colorGreen |
| $blue           | $colorBlue |
| $colour         | $fontColorIndex |
| $fontset        | $fontImage |
| $x              | $drawX |
| $y              | $drawY |
| $fontx          | $fontWidth |
| $fonty          | $fontHeight |
| $nfo            | $nfoLines |
| $image_height   | $imageHeight |
| $image_width    | $imageWidth |
| $c              | $lineIndex |
| $line           | $nfoLine |
| $temp_len       | $lineLength |
| $im             | $imageResource |
| $bgc            | $backgroundColor |
| $i              | $charIndex |
| $current_char   | $currentChar |
| $offset         | $charOffset |
### userhistory.php

| Obfuscated Name | Cleartext Name |
|-----------------|----------------|
| $P              | $commentPage   |
### userdetails.php

| Obfuscated Name | Cleartext Name |
|-----------------|----------------|
| $dt             | $userTimeoutDelta |
| $sr             | $shareRatio |
| $s              | $shareRatioSmiley |
| $ratioimage     | $shareRatioImage |
| $ratio          | $shareRatioDisplay |
| $vm             | $visitorMessage |
| $vAvatar        | $visitorAvatar |
| $vAdded         | $visitorAdded |
| $vPoster        | $visitorPoster |
| $vMessage       | $visitorMessageText |
| $CA             | $contactArray |
| $IM             | $instantMessengerDisplay |
| $SG             | $socialGroup |
| $SGQuery        | $socialGroupQuery |
| $RecentVisitorsArray | $recentVisitorsArray |
| $RV             | $recentVisitor |
| $UserProfileOptions | $userProfileOptions |
### users.php

| Obfuscated Name | Cleartext Name |
|-----------------|----------------|
| $dt             | $userTimeoutDelta |
### usercp.php

| Obfuscated Name | Cleartext Name |
|-----------------|----------------|
| $act            | $userAction    |
| $do             | $userDoAction  |
| $userid         | $userId        |
| $IsStaff        | $isStaff       |
| $contents       | $pageContents  |
| $main           | $mainContent   |
| $substhreads    | $subscribedThreads |
| $substorrents   | $subscribedTorrents |
| $allowed_types  | $allowedImageTypes |
| $AllowedFonts   | $allowedFonts  |
| $AllowedSizes   | $allowedSizes  |
| $ValidFields    | $validFields   |
| $Query          | $profileQuery  |
| $UserProfileOptions | $userProfileOptions |
| $name           | $fieldName     |
| $value          | $fieldValue    |
### upload.php

| Obfuscated Name | Cleartext Name |
|-----------------|----------------|
| $Query          | $editTorrentQuery |
| $EditTorrent    | $editTorrentData |
| $Result         | $detailsResult  |
| $videot         | $videoDetails   |
| $video          | $video          |
| $audiot         | $audioDetails   |
| $audio          | $audio          |
| $query          | $detailsQuery   |
| $WhyNuked       | $nukeReason     |
| $t_image_url    | $torrentImageUrl |
| $t_link         | $torrentLink    |
| $name           | $name           |
| $descr          | $descr          |
| $category       | $category       |
| $offensive      | $offensive      |
| $anonymous      | $anonymous      |
| $free           | $free           |
| $silver         | $silver         |
| $doubleupload   | $doubleUpload   |
| $allowcomments  | $allowComments  |
| $sticky         | $sticky         |
| $isrequest      | $isRequest      |
| $isnuked        | $isNuked        |
| $directdownloadlink | $directDownloadLink |
| $usergroups     | $usergroups     |
| $CURUSER        | $CURUSER        |
| $is_mod         | $is_mod         |
| $TSSEConfig     | $TSSEConfig     |
| $lang           | $lang           |
| $AnnounceURL    | $AnnounceURL    |
| $ModerateTorrent| $ModerateTorrent|
| $CanUploadExternalTorrent | $CanUploadExternalTorrent |
| $max_torrent_size | $max_torrent_size |
| $xbt_active     | $xbt_active     |
| $xbt_announce_url | $xbt_announce_url |
| $announce_urls  | $announce_urls  |
| $externalscrape | $externalscrape |
| $INC_PATH       | $INC_PATH       |
| $use_torrent_details | $use_torrent_details |
| $UploadErrors   | $UploadErrors   |
| $postoptions    | $postoptions    |
| $postoptionstitle | $postoptionstitle |
| $GLOBALS        | $GLOBALS        |
| $GLOBALS["DatabaseConnect"] | $GLOBALS["DatabaseConnect"] |
| $userPermQuery  | $userPermQuery  |
| $isExternalTorrent | $isExternalTorrent |
| $useNfoAsDescription | $useNfoAsDescription |
| $torrentImageFile | $torrentImageFile |
| $isSceneValue   | $isSceneValue   |
| $nfoContents    | $nfoContents    |
### ts_tutorials.php

| Obfuscated Name | Cleartext Name |
|-----------------|----------------|
| $do             | $action        |
| $str            | $tutorialsOutput |
| $Tutorials      | $tutorialsHtml |
| $Title          | $pageTitle     |
| $errors         | $tutorialErrors |
| $EViews         | $editorViews   |
| $prvp           | $previewHtml   |
| $ETitle         | $editorTitle   |
| $EContent       | $editorContent |
| $edited         | $tutorialEdited |
| $Tid            | $tutorialId    |
| $CID            | $commentId     |
| $Tut            | $tutorialData  |
| $Query          | $tutorialQuery/$commentQuery/$tutorialListQuery |
| $count1         | $commentCount  |
| $pagertop1      | $pagerTopComments |
| $pagerbottom1   | $pagerBottomComments |
| $limit1         | $limitComments |
| $PostComments   | $postCommentsHtml |
| $TutorialComments | $tutorialCommentsHtml |
| $dimagedir      | $imageDir      |
| $QuickEditor    | $quickEditor   |
| $newtutorial    | $newTutorialButton |
| $backbutton     | $backButton    |
| $Count          | $tutorialCount |
| $perpage        | $tutorialsPerPage |
| $pagertop       | $pagerTop      |
| $pagerbottom    | $pagerBottom   |
| $limit          | $tutorialsLimit |
| $Comment        | $commentData   |
| $Comments       | $commentData   |
| $EditComment    | $editCommentHtml |
| $Poster         | $posterHtml    |
| $DateContent    | $dateContent   |
| $PostComments   | $postCommentsHtml |
| $TutorialComments | $tutorialCommentsHtml |
| $previewHtml    | $previewHtml   |
| $tutorialEdited | $tutorialEdited |
| $editorTitle    | $editorTitle   |
| $editorContent  | $editorContent |
| $editorViews    | $editorViews   |
| $tutorialErrors | $tutorialErrors |
| $tutorialQuery  | $tutorialQuery |
| $tutorialId     | $tutorialId    |
| $commentId      | $commentId     |
| $tutorialData   | $tutorialData  |
| $tutorialListQuery | $tutorialListQuery |
| $tutorialCount  | $tutorialCount |
| $tutorialsPerPage | $tutorialsPerPage |
| $pagerTop       | $pagerTop      |
| $pagerBottom    | $pagerBottom   |
| $tutorialsLimit | $tutorialsLimit |
| $quickEditor    | $quickEditor   |
| $newTutorialButton | $newTutorialButton |
| $backButton     | $backButton    |
| $tutorialsOutput | $tutorialsOutput |
| $tutorialsHtml  | $tutorialsHtml |
| $pageTitle      | $pageTitle     |
| postLicenseRequest | fetchLicenseResponse |
- install/install.php: check_license function and $licenseRequest assignment fully deobfuscated (2026-01-18)
- install/install.php: All lines processed
+ install/install.php: check_license function, $licenseRequest, and $licenseResponse processing fully deobfuscated and annotated with cleartext names and comments (2026-01-18)
	- postLicenseRequest renamed to fetchLicenseResponse
	- $licenseResponse logic annotated for clarity and future upgrades
	- All regex and error handling now use cleartext variable names and are documented
	- No obfuscated names remain in license logic
# Deobfuscation Progress Log

## Date: 2026-01-18

### Task: Remove all obfuscated names from the codebase

#### Status: In Progress

#### Mapping Table (install/install.php)

| Obfuscated Name | Cleartext Name |
|-----------------|----------------|
| _obfuscated_0D04211C311A323F1C310E17132E1E2A053D1F15370101_ | initializeInstaller |
| _obfuscated_0D222B170D3110372413011630170D2A0E2D0F1D2C2922_ | showErrorMessage |
| $_obfuscated_0D35012C3D051B24251A3E0B2217335B3B1E05042A4022_ | currentChar |
| $_obfuscated_0D21011728181C063F3235230D393D5B1A252F13350A01_ | keyChar |
| $_obfuscated_0D360B0307291739280E350D371B1E31315B4032382822_ | serverName |
| _obfuscated_0D0D243B0511092C0905182B315C3F160339240A060322_ | checkInstallMatch |
| _obfuscated_0D33150237031B271838253E113F5C2C24290237301911_ | decodeLicenseApiString |
| $_obfuscated_0D3415221513122B370E162435241B011B3B1B3D2D2C32_ | allowedHosts |
| $_obfuscated_0D30082D1F16192612112E38363F3B013E021238375C01_ | hostEntry |
| $_obfuscated_0D35232F383B040F330330361F3137030D0F322C3D1A11_ | licenseKey |
| $_obfuscated_0D05181B0A26252E5B013D180A3C312B25133119061622_ | accountDetails |
| $_obfuscated_0D380337300C2E2C130E0108210A283F2B0A01022E2D32_ | licenseError |
| $_obfuscated_0D08023319302629243D135B3D30251E120E0E32303B11_ | licenseRequest |
| $_obfuscated_0D191B1E212B050F0A131C1F031A0D010D2E173E140D32_ | licenseResponse |
| $_obfuscated_0D0F031E0721355C0C082E3D3440260D0634122E271522_ | invalidKeyFlag |
| $_obfuscated_0D28051F3D023F16063E0111141A2E40112908161D1701_ | licenseForm |
| _obfuscated_0D2B32313201080B23120F2D2D091317331C3E402D0901_ | runInstaller |
| _obfuscated_0D192323110E3504131D190138092F070D0C160C183111_ | stepWelcome |
| _obfuscated_0D282B2F041F1C5B25310E1C5B3225322F1E24012E1E01_ | stepDatabase |
| _obfuscated_0D14132F170B2B3040371B120204162C1B1B5B03023301_ | stepConfig |
| _obfuscated_0D051D18030B3B2A2712383516045B17361C291B153F22_ | stepAdmin |
| _obfuscated_0D2911172B28083E3B10324034240D170E12333E152201_ | stepFinish |
| _obfuscated_0D09062F1C160B01283B224012263D1E1F1D3011072332_ | stepCleanup |
| _obfuscated_0D3B2113155C2B240B1D272F3037122D1C0C2C1E5C1A22_ | stepSummary |
| _obfuscated_0D263E2E235B24270B0A25391E0F27073B1E0F1F1A1101_ | stepLicense |
| _obfuscated_0D060901051D080201322C3B162A1A39232E0C2A051B22_ | sendLicenseRequest |
| $_obfuscated_0D1507331840050F3E0D21110423061635023F121E1032_ | licenseUrl |
| $_obfuscated_0D332836264026231D381D37261A062B3C35291D342E11_ | licenseHost |
| $_obfuscated_0D26303C0B0D0D3C252F3F012C143409021634302F1C11_ | licensePath |
| $_obfuscated_0D06243F3C0E143B1A061F06365C342706360C1E041911_ | userAgent |
| $_obfuscated_0D17232324301227332B5B062912142A13293C063C2D32_ | referer |
| $_obfuscated_0D2E080E35362C352B083E5C03163715122E4030221532_ | connectTimeout |
| $_obfuscated_0D3F2326262D2A170A14033B06392D5C3D253101050A11_ | curlResult |
| $_obfuscated_0D19283B30303B0A0D32073421082A2D25380E332D3F32_ | fsock |
| $_obfuscated_0D1B0F09023918103E3B35025C1130093E115C26343F11_ | fsockError |
| $_obfuscated_0D093526350E3C2D13160C1214011F2B18293D34280322_ | fsockErrorStr |
| $_obfuscated_0D401113120B333323262C250F3D253925092E1B101411_ | httpRequest |

---

- install/install.php: First 100 lines deobfuscated and replaced with cleartext names (2026-01-18)
- install/install.php: Lines 101-300 deobfuscated and replaced with cleartext names (2026-01-18)
- install/install.php: Lines 301-500 deobfuscated and replaced with cleartext names (2026-01-18)
- install/install.php: Lines 501-800 deobfuscated and replaced with cleartext names (2026-01-18)
- install/install.php: check_license function and $licenseRequest assignment fully deobfuscated (2026-01-18)
- install/install.php: All lines processed
- Other files: To be processed
- install/install.php: First 100 lines deobfuscated and replaced with cleartext names (2026-01-18)
- install/install.php: Lines 101-300 deobfuscated and replaced with cleartext names (2026-01-18)
- install/install.php: Lines 301-500 deobfuscated and replaced with cleartext names (2026-01-18)
- install/install.php: Lines 501-800 deobfuscated and replaced with cleartext names (2026-01-18)
- install/install.php: All lines processed
- Other files: To be processed

---

## install/upgrade.php (first 100 lines)

| Obfuscated Name                                      | Cleartext Name         |
|------------------------------------------------------|------------------------|
| _obfuscated_FF9BA38F97BEC09E8DADBC8B97B7B7B1BB98979682B791_ | SafeModeChecker        |
| _obfuscated_FF9187A690B7B1BC8EB486C0A8B7B3928ABBA89A95B691_ | LicenseCipher          |
| _obfuscated_FFAD8BA18BB199B684989BA99BB59CA593B6A5B99FB0A1_  | encode                 |
| _obfuscated_FFBE958DB99D8390B8ABA38AAF928C9EA4B5AF9483BDB1_  | decode                 |
| _obfuscated_0D16081C03083337322E0E3D3340373F2A3D1B350F0522_  | initErrorReporting     |
| _obfuscated_0D031434370D3E1E1F2F341C1D3E091B0E0340021F2B22_  | initSession            |
| _obfuscated_0D021303302D0C2D2830341802361418225B3F1A061101_  | getSessionName         |
| _obfuscated_0D5B1F1028061E063E0F02273C1D1C29150E041E263F22_  | defineScriptConstants  |
| _obfuscated_0D1F1E102C2930062B2D0E052F2D32222C1E3C0D152201_  | checkEnvironment       |
| _obfuscated_0D1809010A2117082E15133B26233129260C3209131501_  | initUpgradeStep1       |
| _obfuscated_0D2907372A1D2A0B182703322A020E0429320930150D01_  | initUpgradeStep2       |
| _obfuscated_0D075B0F0C0D323F122329150C282C293B021415291032_  | initUpgradeStep3       |
| _obfuscated_0D215B0C3C01070739080935401B140821243508393B01_  | initUpgradeStep4       |

**Note:** All replacements above are reflected in the first 100 lines of install/upgrade.php. The process will continue for the rest of the file and other files in the codebase.

---

## install/install.php Deobfuscation Progress (Step Handler)

| Obfuscated Name                                      | Clear Name           |
|------------------------------------------------------|----------------------|
| _obfuscated_0D2B32313201080B23120F2D2D091317331C3E402D0901_ | handleInstallStep    |
| _obfuscated_0D192323110E3504131D190138092F070D0C160C183111_ | showWelcomeScreen    |
| _obfuscated_0D282B2F041F1C5B25310E1C5B3225322F1E24012E1E01_ | checkRequirements    |
| _obfuscated_0D14132F170B2B3040371B120204162C1B1B5B03023301_ | confirmDatabase      |
| _obfuscated_0D051D18030B3B2A2712383516045B17361C291B153F22_ | createTables         |
| _obfuscated_0D2911172B28083E3B10324034240D170E12333E152201_ | populateTables       |
| _obfuscated_0D09062F1C160B01283B224012263D1E1F1D3011072332_ | configureTracker     |
| _obfuscated_0D3B2113155C2B240B1D272F3037122D1C0C2C1E5C1A22_ | setupAdminAccount    |
| _obfuscated_0D263E2E235B24270B0A25391E0F27073B1E0F1F1A1101_ | finishSetup          |

**Note:** All usages and definitions for these functions have been updated in the main step handler. Continue with all other function and variable names throughout the file.

---

*This log will be updated as progress continues.*

---

| Obfuscated Name | Cleartext Name |
|-----------------|----------------|
| _obfuscated_0D060901051D080201322C3B162A1A39232E0C2A051B22_ | sendLicenseRequest |
| _obfuscated_0D400F233E342139212B262D1F2E153221311D3F332522_ | buildLicenseQueryString |
| _obfuscated_0D103C24022F1C23352E0C2504282C2214284029253932_ | validateInstallKey |
| _obfuscated_0D1133100E343106180A262F18282C0E330803070F0411_ | generateRandomString |
| _obfuscated_021D22_ | getCurrentDateTime |
| _obfuscated_0D16191B0F351C38383023240A06020D132C165B1B2232_ | escapeSqlValue |
| _obfuscated_0D293030053E5B073804252E37302D231B081831351C01_ | createAdminUser |
| _obfuscated_0D5B162A2D280F3C250E10071E0D3637072C3438210511_ | customEncrypt |
| _obfuscated_2C24290237301911_ | customDecrypt |
| _obfuscated_0D1B191D081F261B3506222A3D2413161D341916380932_ | flushOutputBuffer |

*All function names in install/install.php have been deobfuscated and replaced with descriptive names. This table will be updated as further deobfuscation progresses in other files.*

---

### Deobfuscation Mapping (install/install.php, next batch)

| Obfuscated Name                                      | Cleartext Name                |
|------------------------------------------------------|-------------------------------|
| _obfuscated_0D11212121213E1B2B02301036125C3126151F39340701_ | encodeInstallString           |
| _obfuscated_0D23162216172F16332C253206115B14270F1B37101F11_ | renderInstallPage             |
| _obfuscated_0D5C103B0109133D081C0215110E221E1A0402350C2201_ | renderInstallFooter           |
| _obfuscated_0D2A0F04393D26312623242C2F35323D34221C24312922_ | getInstallIpAddress           |
| _obfuscated_0D1F1E102C2930062B2D0E052F2D32222C1E3C0D152201_ | initializeInstallSession      |
| _obfuscated_0D32130712161C18301A3807105B2315372F2B010D3D22_ | renderRequirementRow          |

All usages and definitions of these function names have been updated to their new descriptive names. This continues the systematic removal of all obfuscated names from install/install.php.

---

### Deobfuscation Mapping (install/install.php, requirements/permissions section)

| Obfuscated Name                                      | Cleartext Name                |
|------------------------------------------------------|-------------------------------|
| $_obfuscated_0D3C330B03163E022110103D0F2E243F0A2D33113F2C11_ | $fileList                     |
| $_obfuscated_0D2930241A172D2B30015B1823161D5C242803321E2F01_ | $filePath                     |
| _obfuscated_0D072A1C1D0E5B1D3E2A1D0D252F1D0A060C3F140B2232_  | getFileExtension              |
| _obfuscated_0D0805330C160E2F0C32243E251A210105013616041832_ | renderInstallStepMessage      |

All usages and definitions of these variable and function names in the requirements/permissions section have been updated to their new descriptive names. This continues the systematic removal of all obfuscated names from install/install.php.

---

### Final Batch Deobfuscation in install/install.php

| Obfuscated Name                                      | Cleartext Name                |
|------------------------------------------------------|-------------------------------|
| $_obfuscated_0D0802190A3604250C1C033F400C0D3514262A3C1D1811_ | $errorMessages                 |
| _obfuscated_0D0805330C160E2F0C32243E251A210105013616041832_   | renderWelcomeScreenNote        |
| _obfuscated_0D23162216172F16332C253206115B14270F1B37101F11_   | showWelcomeScreen              |
| _obfuscated_0D5C103B0109133D081C0215110E221E1A0402350C2201_   | showFooter                     |
| _obfuscated_0D21102A0335113F0634231E010B222E32100E312A1B22_   | checkDatabaseConnection        |
| _obfuscated_0D222B170D3110372413011630170D2A0E2D0F1D2C2922_   | showInstallerError             |
| _obfuscated_0D5B162A2D280637111D13290D2D1A3B27322138161932_   | checkTrackerFilePermissions    |
| _obfuscated_0D2A030F193F39011F29271538193B35080C0331322E22_   | finalizeInstallCleanup         |
| _obfuscated_0D1B040E38063C3111160B050D2C012D1D320E2B1E0211_   | $finishMessage                 |
| _obfuscated_FFAD8BA18BB199B684989BA99BB59CA593B6A5B99FB0A1_   | encryptString                  |
| $_obfuscated_0D380F18170A2C3B023F2504403F0C30211D36223E3E01_  | $stringLength                  |
| $_obfuscated_0D39125C23103E37300B01123007260C2B3C1C24080F11_  | $keyLength                     |
| $_obfuscated_0D31011F1A1F0A1E40080D35332D213423113814213D01_  | $charCode                      |
| $_obfuscated_0D06300B35103C2B3C5C2E40361540312E5C1F3D145B32_  | $keyCharCode                   |

---

### faq.php
| Obfuscated Name | Cleartext Name |
|-----------------|----------------|
| $do             | $faqAction     |
| $faq_errors     | $faqErrors     |
| $words          | $searchWords   |
| $searchtype     | $searchType    |
| $extra          | $searchExtra   |
| $query          | $faqQuery      |
| $id             | $faqId         |
| $faq            | $faqRow        |
| $faqListQuery   | $faqListQuery  |
| $faqListRow     | $faqListRow    |
### tinymce_emotions.php
| Obfuscated Name | Cleartext Name |
|-----------------|----------------|
| $Q              | $configQuery   |
| $Result         | $configResult  |

### ts_ajax9.php
| Obfuscated Name | Cleartext Name |
|-----------------|----------------|
| $do             | $ajaxAction    |
| $value          | $ajaxValue     |
| $query          | $ajaxQuery     |
| $Results        | $ajaxResults   |
| $torrent        | $torrentRow    |

### index.php
| Obfuscated Name | Cleartext Name |
|-----------------|----------------|
| $P              | $pluginRow     |
| $PluginCache    | $pluginCache   |
| $Plugins        | $plugin        |
| $Plugins_LEFT   | $pluginsLeft   |
| $Plugins_MIDDLE | $pluginsMiddle |
| $Plugins_RIGHT  | $pluginsRight  |

### usercp.php
| Obfuscated Name | Cleartext Name |
|-----------------|----------------|
| $do             | $userDoAction  |
| $act            | $userAction    |
| $i              | $moodIndex     |
| $Js             | $moodJs        |
| $main           | $mainContent   |
| $Query          | $profileQuery  |

### uploaderform.php
| Obfuscated Name | Cleartext Name     |
|-----------------|--------------------|
| $ID             | $uploadSpeedId     |
| $Content        | $uploadSpeedContent|

### ts_tutorials.php
| Obfuscated Name | Cleartext Name     |
|-----------------|--------------------|
| $do             | $tutorialAction    |
| $Tid            | $tutorialId        |
| $Query          | $tutorialQuery     |
| $Tut            | $tutorialRow       |

### upload.php
| Obfuscated Name | Cleartext Name     |
|-----------------|--------------------|
| $vt             | $videoTrack/$audioTrack |
| $id             | $externalTorrentId |
| $to             | $emailRecipients   |
| $arr            | $emailRow          |
| $sm             | $emailSendResult   |
