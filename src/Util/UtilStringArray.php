<?php declare(strict_types=1);

namespace Mcx\EventList\Util;

class UtilStringArray
{

    /**
     * explodes and trims results .. excludes empty items ...
     * example:
     * "a, b, c, ,d" returns [a,b,c,d]
     *
     * @param string $delimiter
     * @param string $string
     * @param int|null $limit
     * @return array
     */
    public static function trimExplode(string $delimiter, string $string, $limit = null, $bKeepEmpty = false)
    {
        if (is_null($limit)) {
            $chunksArr = explode($delimiter, $string);
        } else {
            $chunksArr = explode($delimiter, $string, $limit);
        }

        $newChunksArr = [];
        foreach ($chunksArr as $value) {
            if (strcmp('', trim($value)) || $bKeepEmpty) {
                $newChunksArr[] = trim($value);
            }
        }
        reset($newChunksArr);

        return $newChunksArr;
    }
}