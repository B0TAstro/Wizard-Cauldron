<?php
namespace App\Service;

use App\Entity\Spell;

final class RngService
{
/**
     * @param array<string, Spell[]> $poolByRarity
     * @param array<string, int> $weights
     */
    public function pick(array $poolByRarity, array $weights): ?Spell
    {
        $filteredWeights = [];
        foreach ($weights as $rarity => $w) {
            if (!empty($poolByRarity[$rarity])) {
                $filteredWeights[$rarity] = max(0, (int)$w);
            }
        }
        if (!$filteredWeights) return null;

        $sum = array_sum($filteredWeights);
        $needle = mt_rand(1, max(1, $sum));
        $acc = 0;
        $chosen = array_key_first($filteredWeights);

        foreach ($filteredWeights as $rarity => $w) {
            $acc += $w;
            if ($needle <= $acc) { $chosen = $rarity; break; }
        }

        $bucket = $poolByRarity[$chosen] ?? [];
        if (!$bucket) return null;

        return $bucket[array_rand($bucket)];
    }
}