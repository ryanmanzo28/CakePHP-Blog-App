<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\Article;
use App\Model\Entity\User;
use Cake\ORM\Locator\LocatorAwareTrait;

class ContentModerationService
{
    use LocatorAwareTrait;

    /**
     * Detect matching filters/actions for one article without side effects.
     *
     * @param \App\Model\Entity\Article $article Article to evaluate.
     * @return array<string, mixed>
     */
    public function detectMatches(Article $article): array
    {
        $ModerationFilters = $this->fetchTable('ModerationFilters');

        $text = mb_strtolower(trim((string)$article->title . ' ' . (string)$article->body));
        if ($text === '') {
            return [
                'keywords' => [],
                'delete' => false,
                'silence' => false,
                'ban' => false,
            ];
        }

        $filters = $ModerationFilters->find()
            ->where(['active' => true])
            ->all();

        $matchedKeywords = [];
        $shouldDelete = false;
        $shouldSilence = false;
        $shouldBan = false;

        foreach ($filters as $filter) {
            $keyword = mb_strtolower(trim((string)$filter->keyword));
            if ($keyword === '') {
                continue;
            }

            if (mb_stripos($text, $keyword) === false) {
                continue;
            }

            $matchedKeywords[] = $filter->keyword;
            $shouldDelete = $shouldDelete || (bool)$filter->action_delete;
            $shouldSilence = $shouldSilence || (bool)$filter->action_silence;
            $shouldBan = $shouldBan || (bool)$filter->action_ban;
        }

        return [
            'keywords' => array_values(array_unique($matchedKeywords)),
            'delete' => $shouldDelete,
            'silence' => $shouldSilence,
            'ban' => $shouldBan,
        ];
    }

    /**
     * Apply active moderation filters to one article.
     *
     * @param \App\Model\Entity\Article $article Article to evaluate.
     * @return array<string, mixed>
     */
    public function moderateArticle(Article $article): array
    {
        $Articles = $this->fetchTable('Articles');
        $Users = $this->fetchTable('Users');
        $matches = $this->detectMatches($article);
        $shouldDelete = (bool)$matches['delete'];
        $shouldSilence = (bool)$matches['silence'];
        $shouldBan = (bool)$matches['ban'];

        $bannedUser = false;
        if ($shouldBan && (int)$article->user_id > 0) {
            $user = $Users->find()->where(['id' => (int)$article->user_id])->first();
            if ($user instanceof User && $user->role !== User::ROLE_BANNED) {
                $user->role = User::ROLE_BANNED;
                $Users->save($user);
                $bannedUser = true;
            }
        }

        if ($Articles->getSchema()->hasColumn('silenced')) {
            $article->silenced = $shouldSilence;
            $Articles->save($article, ['checkRules' => false]);
        }

        $deleted = false;
        if ($shouldDelete) {
            $deleted = (bool)$Articles->delete($article);
        }

        return [
            'matched' => $matches['keywords'],
            'deleted' => $deleted,
            'silenced' => $shouldSilence,
            'bannedUser' => $bannedUser,
        ];
    }

    /**
     * Reprocess moderation for all articles.
     *
     * @return array<string, int>
     */
    public function reprocessAllArticles(): array
    {
        $Articles = $this->fetchTable('Articles');

        $reviewed = 0;
        $deleted = 0;
        $silenced = 0;
        $banned = 0;

        foreach ($Articles->find()->all() as $article) {
            $reviewed++;
            $result = $this->moderateArticle($article);
            if (!empty($result['deleted'])) {
                $deleted++;
            }
            if (!empty($result['silenced'])) {
                $silenced++;
            }
            if (!empty($result['bannedUser'])) {
                $banned++;
            }
        }

        return compact('reviewed', 'deleted', 'silenced', 'banned');
    }
}
