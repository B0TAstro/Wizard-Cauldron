<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserSpell;
use App\Repository\UserSpellRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

final class MysteryTradeService
{
    private const QUEUE_KEY = 'mystery_trade_queue';

    public function __construct(
        private CacheInterface $cache,
        private EntityManagerInterface $em,
        private UserSpellRepository $userSpells,
    ) {}

    public function getTicket(User $user): ?array
    {
        return $this->cache->get($this->ticketKey($user), fn() => null);
    }

    public function join(User $user): array
    {
        $ticket = $this->getTicket($user);
        if ($ticket && ($ticket['status'] ?? null) !== 'done') {
            return $ticket;
        }

        $queue = $this->cache->get(self::QUEUE_KEY, fn(CacheItemInterface $i) => []);
        if (!\in_array($user->getId(), $queue, true)) {
            $queue[] = $user->getId();
            $this->cache->delete(self::QUEUE_KEY);
            $this->cache->get(self::QUEUE_KEY, fn() => $queue);
        }

        $queue = $this->cache->get(self::QUEUE_KEY, fn() => []);
        if (\count($queue) >= 2) {
            $me = $user->getId();
            $partnerId = null;

            foreach ($queue as $idx => $uid) {
                if ($uid !== $me) { $partnerId = $uid; unset($queue[$idx]); break; }
            }
            foreach ($queue as $idx => $uid) {
                if ($uid === $me) { unset($queue[$idx]); break; }
            }

            $queue = array_values($queue);
            $this->cache->delete(self::QUEUE_KEY);
            $this->cache->get(self::QUEUE_KEY, fn() => $queue);

            if ($partnerId) {
                /** @var User $partner */
                $partner = $this->em->getReference(User::class, $partnerId);
                return $this->attemptTrade($user, $partner);
            }
        }

        $waiting = ['status' => 'waiting'];
        $this->cache->delete($this->ticketKey($user));
        $this->cache->get($this->ticketKey($user), fn() => $waiting);
        return $waiting;
    }

    public function cancel(User $user): void
    {
        $queue = $this->cache->get(self::QUEUE_KEY, fn() => []);
        $queue = array_values(array_filter($queue, fn($id) => $id !== $user->getId()));
        $this->cache->delete(self::QUEUE_KEY);
        $this->cache->get(self::QUEUE_KEY, fn() => $queue);
        $this->cache->delete($this->ticketKey($user));
    }

    private function attemptTrade(User $a, User $b): array
    {
        $idsA = $this->userSpells->findOwnedSpellIds($a);
        $idsB = $this->userSpells->findOwnedSpellIds($b);

        if (!$idsA || !$idsB) {
            $fail = ['status' => 'failed', 'reason' => 'one_empty'];
            $this->setTicket($a, $fail); $this->setTicket($b, $fail);
            return $fail;
        }

        $candA = array_values(array_diff($idsA, $idsB));
        $candB = array_values(array_diff($idsB, $idsA));

        $pickFromA = $candA ?: $idsA;
        $pickFromB = $candB ?: $idsB;

        $idA = $pickFromA[array_rand($pickFromA)];
        $idB = $pickFromB[array_rand($pickFromB)];

        /** @var UserSpell $rowA */
        $rowA = $this->userSpells->findOneBy(['user' => $a, 'spell' => $idA]);
        /** @var UserSpell $rowB */
        $rowB = $this->userSpells->findOneBy(['user' => $b, 'spell' => $idB]);

        if (!$rowA || !$rowB) {
            $fail = ['status' => 'failed', 'reason' => 'not_found'];
            $this->setTicket($a, $fail); $this->setTicket($b, $fail);
            return $fail;
        }

        $tries = 8;
        while ($tries--) {
            if (!$this->userSpells->existsForUser($b, $rowA->getSpell())
             && !$this->userSpells->existsForUser($a, $rowB->getSpell())) {
                break;
            }
            $idA = $idsA[array_rand($idsA)];
            $idB = $idsB[array_rand($idsB)];
            $rowA = $this->userSpells->findOneBy(['user' => $a, 'spell' => $idA]);
            $rowB = $this->userSpells->findOneBy(['user' => $b, 'spell' => $idB]);
        }

        if ($this->userSpells->existsForUser($b, $rowA->getSpell())
         || $this->userSpells->existsForUser($a, $rowB->getSpell())) {
            $fail = ['status' => 'failed', 'reason' => 'duplicates_only'];
            $this->setTicket($a, $fail); $this->setTicket($b, $fail);
            return $fail;
        }

        $rowA->setUser($b);
        $rowB->setUser($a);
        $this->em->flush();

        $okA = [
            'status'   => 'done',
            'gave'     => $rowA->getSpell()->getName(),
            'received' => $rowB->getSpell()->getName(),
            'partner'  => $b->getPseudo(),
        ];
        $okB = [
            'status'   => 'done',
            'gave'     => $rowB->getSpell()->getName(),
            'received' => $rowA->getSpell()->getName(),
            'partner'  => $a->getPseudo(),
        ];
        $this->setTicket($a, $okA);
        $this->setTicket($b, $okB);

        return $okA;
    }

    private function setTicket(User $user, array $data): void
    {
        $this->cache->delete($this->ticketKey($user));
        $this->cache->get($this->ticketKey($user), fn() => $data);
    }

    private function ticketKey(User $user): string
    {
        return 'trade_ticket_'.$user->getId();
    }
}