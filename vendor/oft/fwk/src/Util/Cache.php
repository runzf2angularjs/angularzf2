<?php
/**
 * Copyright (C) 2015 Orange
 *
 * This software is confidential and proprietary information of Orange.
 * You shall not disclose such Confidential Information and shall use it only
 * in accordance with the terms of the agreement you entered into.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * If you are Orange employee you shall use this software in accordance with
 * the Orange Source Charter (http://opensource.itn.ftgroup/index.php/Orange_Source).
 */

namespace Oft\Util;

class Cache
{
    /**
     * Vide le cache de l'application
     * 
     * @return int
     * @throws \RuntimeException
     */
    public static function clearCache()
    {
        if (!is_dir(CACHE_DIR) || !is_writeable(CACHE_DIR)) {
            throw new \RuntimeException('Cache dir is not writeable');
        }
        
        $count = 0;
        foreach (glob(CACHE_DIR . '/*') as $filename) {
            $shouldUnlink = false;
            $basename = basename($filename);

            if ($basename == 'config.php') {
                $shouldUnlink = true;
            } else if (strlen($basename) == 32) { // Assetic cache
                $shouldUnlink = true;
            } else if (substr($basename, 0, 4) == 'Oft-') { // CachedFactory
                $shouldUnlink = true;
            }

            if ($shouldUnlink) {
                unlink($filename);
                $count++;
            }
        }
        
        return $count;
    }
   

}
