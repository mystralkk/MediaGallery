<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - Geeklog                                           |
// +--------------------------------------------------------------------------+
// | lightbox.php                                                             |
// |                                                                          |
// | Lightbox slideshow                                                       |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2015 by the following authors:                             |
// |                                                                          |
// | Yoshinori Tahara       taharaxp AT gmail DOT com                         |
// |                                                                          |
// | Based on the Media Gallery Plugin for glFusion CMS                       |
// | Copyright (C) 2002-2010 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | This program is free software; you can redistribute it and/or            |
// | modify it under the terms of the GNU General Public License              |
// | as published by the Free Software Foundation; either version 2           |
// | of the License, or (at your option) any later version.                   |
// |                                                                          |
// | This program is distributed in the hope that it will be useful,          |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
// | GNU General Public License for more details.                             |
// |                                                                          |
// | You should have received a copy of the GNU General Public License        |
// | along with this program; if not, write to the Free Software Foundation,  |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.          |
// |                                                                          |
// +--------------------------------------------------------------------------+

require_once '../lib-common.php';

if (!in_array('mediagallery', $_PLUGINS)) {
    COM_redirect($_CONF['site_url'] . '/index.php');
}
if (COM_isAnonUser() && $_MG_CONF['loginrequired'] == 1) {
    exit;
}

require_once $_CONF['path'] . 'plugins/mediagallery/include/common.php';

$aid = isset($_REQUEST['aid']) ? COM_applyFilter($_REQUEST['aid'], true) : 0;
$src = isset($_REQUEST['src']) ? COM_applyFilter($_REQUEST['src'])       : 'disp';
if ($src != 'disp' && $src != 'orig') {
    $src = 'tn';
}

$album_data = MG_getAlbumData($aid, array('album_id'), true);

$xml = '';
$xml .= "<slides>\n";
if (isset($album_data['album_id']) && $album_data['access'] >= 1) {
    $sql = MG_buildMediaSql(array(
        'album_id' => $aid,
        'fields'   => array('media_type', 'media_filename', 'remote_media',
                            'remote_url', 'media_title'),
        'where'    => 'm.include_ss = 1'
    ));
    $result = DB_query($sql);
    while ($A = DB_fetchArray($result)) {
        if ($A['media_type'] != 0) continue;
        $PhotoPath = MG_getFilePath($src, $A['media_filename']);
        $ext = pathinfo($PhotoPath, PATHINFO_EXTENSION);
        $PhotoURL  = MG_getFileUrl($src, $A['media_filename'], $ext);
        $imgsize = @getimagesize($PhotoPath);
        if ($imgsize == false && $A['remote_media'] != 1) continue;
        if ($A['remote_media'] == 1) {
            $PhotoURL = $A['remote_url'];
        }
        $caption = htmlentities(strip_tags($A['media_title']), ENT_QUOTES, COM_getCharset());
        $xml .= '<slide src="' . $PhotoURL . '" caption="' . $caption . '"/>' . "\n";
    }
}
$xml .= "</slides>\n";

header("Content-type: text/xml; charset=" . COM_getCharset());
echo $xml;
?>