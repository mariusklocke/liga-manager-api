<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Functional\Framework;

use Psr\Http\Message\ResponseInterface;
use stdClass;

class RichClient
{
    /** @var HttpClient */
    private $httpClient;

    /** @var array */
    private $headers;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->headers    = [];
    }

    public function setBasicAuth(): void
    {
        $this->headers['Authorization'] = 'Basic ' . base64_encode('admin:admin');
    }

    public function clearAuth(): void
    {
        unset($this->headers['Authorization']);
    }

    public function createSeason(string $name): stdClass
    {
        return $this->decodeBody($this->httpClient->post('/api/season', ['name' => $name], $this->headers));
    }

    public function getAllSeasons(): array
    {
        return $this->decodeBody($this->httpClient->get('/api/season'));
    }

    public function getSeason(string $id): stdClass
    {
        return $this->decodeBody($this->httpClient->get('/api/season/' . $id));
    }

    public function getTeamsInSeason(string $seasonId): array
    {
        return $this->decodeBody($this->httpClient->get('/api/season/' . $seasonId . '/team'));
    }

    public function deleteSeason(string $id): void
    {
        $this->handleErrors($this->httpClient->delete('/api/season/' . $id, $this->headers));
    }

    public function startSeason(string $id): void
    {
        $this->handleErrors($this->httpClient->post('/api/season/' . $id . '/start', [], $this->headers));
    }

    public function addTeamToSeason(string $seasonId, string $teamId): void
    {
        $this->handleErrors($this->httpClient->put('/api/season/' . $seasonId . '/team/' . $teamId, [], $this->headers));
    }

    public function removeTeamFromSeason(string $seasonId, string $teamId): void
    {
        $this->handleErrors($this->httpClient->delete('/api/season/' . $seasonId . '/team/' . $teamId));
    }

    public function getSeasonRanking(string $seasonId): stdClass
    {
        return $this->decodeBody($this->httpClient->get('/api/season/' . $seasonId . '/ranking'));
    }

    public function createTeam(string $name): stdClass
    {
        return $this->decodeBody($this->httpClient->post('/api/team', ['name' => $name], $this->headers));
    }

    public function createMatches(string $seasonId, string $startAt): void
    {
        $this->handleErrors($this->httpClient->post(
            '/api/season/' . $seasonId . '/matches',
            ['start_at' => $startAt],
            $this->headers
        ));
    }

    public function getMatch(string $matchId): stdClass
    {
        return $this->decodeBody($this->httpClient->get('/api/match/' . $matchId));
    }

    public function findMatchesByMatchDay(string $seasonId, int $matchDay): array
    {
        $queryParams = [
            'match_day' => $matchDay
        ];
        return $this->decodeBody($this->httpClient->get(
            '/api/season/' . $seasonId . '/matches?' . http_build_query($queryParams)
        ));
    }

    public function submitMatchResult(string $matchId, int $homeScore, int $guestScore): void
    {
        $this->handleErrors($this->httpClient->post(
            '/api/match/' . $matchId . '/result',
            ['home_score' => $homeScore, 'guest_score' => $guestScore],
            $this->headers
        ));
    }

    public function createTournament(string $name): stdClass
    {
        return $this->decodeBody($this->httpClient->post('/api/tournament', ['name' => $name], $this->headers));
    }

    public function getMatchesInTournament(string $tournamentId): array
    {
        return $this->decodeBody($this->httpClient->get('/api/tournament/' . $tournamentId . '/matches'));
    }

    public function getAllTournaments(): array
    {
        return $this->decodeBody($this->httpClient->get('/api/tournament'));
    }

    public function getTournament(string $id): stdClass
    {
        return $this->decodeBody($this->httpClient->get('/api/tournament/' . $id));
    }

    public function setTournamentRound(string $tournamentId, int $round, array $teamPairs, string $plannedFor): void
    {
        $this->handleErrors($this->httpClient->put(
            '/api/tournament/' . $tournamentId . '/round/' . $round,
            [
                'planned_for' => $plannedFor,
                'team_pairs'  => $teamPairs
            ],
            $this->headers
        ));
    }

    public function getAuthenticatedUser(): stdClass
    {
        return $this->decodeBody($this->httpClient->get('/api/user/me', $this->headers));
    }

    public function changePassword(string $newPassword): void
    {
        $this->handleErrors($this->httpClient->put(
            '/user/me/password',
            ['new_password' => $newPassword],
            $this->headers
        ));
    }

    private function decodeBody(ResponseInterface $response)
    {
        $this->handleErrors($response);
        return $this->httpClient->parseBody($response->getBody());
    }

    private function handleErrors(ResponseInterface $response): void
    {
        if ($response->getStatusCode() >= 400) {
            throw new ApiException($response->getReasonPhrase(), $response->getStatusCode());
        }
    }
}