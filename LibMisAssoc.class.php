<?php
/**
 * @file LibMisAssoc.class.php
 * @brief mis关联数据获取:从mis中获取数据后，通过key与详细的数据关联起来
 * 主要对mis中的歌曲数据，专辑数据，艺人数据进行处理，补全其详细信息
 * @author sunhuai(v_sunhuai@baidu.com)
 * @version 1.0
 * @date 2013-2-3 10:53:02
 */

class LibMisAssoc {

    /**
     * @brief 获取歌曲的详细信息
     *
     * @param $misSongInfos
     * @param $offset
     * @param $num
     *
     * @returns 
     */
    public static function getAssocSongInfos($misSongInfos, $offset = 0, $num = 20) {
        $misSongInfos = array_slice($misSongInfos, $offset, $num);
        $assocIds = LibTing::get2DsVal($misSongInfos, 'song_id');
        $AssocLib = LibFactory::getInstance('LibSongEx');
        $baseColumns = array('song_id', 'ting_uid', 'all_artist_ting_uid', 'author', 'album_id', 'album_title', 'pic_small', 'pic_big', 'title', 'artist_id', 'all_artist_id', 'relate_status', 'resource_type', 'copy_type', 'high_rate', 'has_mv');
        $AssocLib->getList($assocIds, $baseColumns);
        return LibTing::array_merge2Ds($misSongInfos, $AssocLib->result, 'song_id');
    }

    public static function getAssocAlbumInfos($misAlbumInfos, $offset = 0, $num = 10) {
        $misAlbumInfos = array_slice($misAlbumInfos, $offset, $num);
        $assocIds = LibTing::get2DsVal($misAlbumInfos, 'album_id');
        $AssocLib = LibFactory::getInstance('LibAlbumEx');
        $baseColumns = array('album_id', 'artist_id', 'all_artist_id','all_artist_ting_uid', 'author', 'title', 'publishcompany','styles', 'publishtime', 'artist_ting_uid', 'pic_small', 'pic_big', 'pic_s130', 'pic_s180', 'relate_status', 'pic_radio', 'info', 'first_release');
        $AssocLib = LibFactory::getInstance('LibAlbumEx');
        $AssocLib->getList($assocIds, $baseColumns);
        return LibTing::array_merge2Ds($misAlbumInfos, $AssocLib->result, 'album_id');
    }

    public static function getAssocArtistInfos($misArtistInfos, $offset = 0, $num = 10) {
        $misArtistInfos = array_slice($misArtistInfos, $offset, $num);
        $assocIds = LibTing::get2DsVal($misArtistInfos, 'artist_id');
        $AssocLib = LibFactory::getInstance('LibArtist');
        $assocInfos = $AssocLib->getArrArtistByArtistIDs($assocIds, TRUE, 0, count($assocIds));
        return LibTing::array_merge2Ds($misArtistInfos, $assocInfos, 'artist_id');
    }

    public static function getAssocInfos($misInfos, $type, $offset = 0, $num = 10) {
        if (!$misInfos || !$type) {
            return $FALSE;
        }
        $misInfos = array_slice($misInfos, $offset, $num);
        $assocKey = '';
        switch ($type) {
            case 'song':
                $assocKey = 'song_id';
                $assocIds = LibTing::get2DsVal($misInfos, $assocKey);
                $AssocLib = LibFactory::getInstance('LibSongEx');
                $baseColumns = array('song_id', 'ting_uid', 'all_artist_ting_uid', 'author', 'album_id', 'album_title', 'pic_small', 'pic_big', 'title', 'artist_id', 'all_artist_id', 'relate_status', 'resource_type', 'copy_type', 'high_rate', 'has_mv');
                $AssocLib->getList($assocIds, $baseColumns);
                return LibTing::array_merge2Ds($misInfos, $AssocLib->result, $assocKey);
                break;
            case 'album':
                $assocKey = 'album_id';
                $assocIds = LibTing::get2DsVal($misInfos, $assocKey);
                $AssocLib = LibFactory::getInstance('LibAlbumEx');
                $baseColumns = array('album_id', 'artist_id', 'all_artist_id','all_artist_ting_uid', 'author', 'title', 'publishcompany','styles', 'publishtime', 'artist_ting_uid', 'pic_small', 'pic_big', 'pic_s130', 'pic_s180', 'relate_status', 'pic_radio', 'info', 'first_release');
                $AssocLib = LibFactory::getInstance('LibAlbumEx');
                $AssocLib->getList($assocIds, $baseColumns);
                return LibTing::array_merge2Ds($misInfos, $AssocLib->result, $assocKey);
                break;
            case 'artist':
                $assocKey = 'artist_id';
                $assocIds = LibTing::get2DsVal($misInfos, 'artist_id');
                $AssocLib = LibFactory::getInstance('LibArtist');
                $assocInfos = $AssocLib->getArrArtistByArtistIDs($assocIds, TRUE, 0, count($assocIds));
                return LibTing::array_merge2Ds($misInfos, $assocInfos, $assocKey);
                break;
            default:
                return FALSE;
                break;
        }
    }

}
