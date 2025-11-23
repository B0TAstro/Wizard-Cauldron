<?php


namespace App\Service;

final class RngService
{
    /**
     * @param array<string,float> $weights  ex: ['common'=>0.6, 'rare'=>0.25, ...]
     * @return string  key tirée
     */
    public function pickWeighted(array $weights): string
    {
        $sum = array_sum($weights);
        $r = mt_rand() / mt_getrandmax() * $sum;
        foreach ($weights as $k => $w) {
            if (($r -= $w) <= 0) return $k;
        }
        return array_key_first($weights);
    }
}
