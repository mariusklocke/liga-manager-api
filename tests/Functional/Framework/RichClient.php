<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Functional\Framework;

use Psr\Http\Message\ResponseInterface;
use stdClass;

class RichClient
{
    /** @var SlimClient */
    private $slimClient;

    /** @var array */
    private $headers;

    public function __construct(SlimClient $httpClient)
    {
        $this->slimClient = $httpClient;
        $this->headers    = [];
    }

    public function setBasicAuth(string $username, string $password): void
    {
        $this->headers['Authorization'] = 'Basic ' . base64_encode($username . ':' . $password);
    }

    public function clearAuth(): void
    {
        unset($this->headers['Authorization']);
    }

    public function createSeason(string $name): stdClass
    {
        return $this->decodeBody($this->slimClient->post('/api/seasons', ['name' => $name], $this->headers));
    }

    public function getAllSeasons(): array
    {
        return $this->decodeBody($this->slimClient->get('/api/seasons'));
    }

    public function getSeason(string $id): stdClass
    {
        return $this->decodeBody($this->slimClient->get('/api/seasons/' . $id));
    }

    public function getTeamsInSeason(string $seasonId): array
    {
        return $this->decodeBody($this->slimClient->get('/api/seasons/' . $seasonId . '/teams'));
    }

    public function deleteSeason(string $id): void
    {
        $this->handleErrors($this->slimClient->delete('/api/seasons/' . $id, $this->headers));
    }

    public function startSeason(string $id): void
    {
        $this->handleErrors($this->slimClient->post('/api/seasons/' . $id . '/start', [], $this->headers));
    }

    public function addTeamToSeason(string $seasonId, string $teamId): void
    {
        $this->handleErrors($this->slimClient->put('/api/seasons/' . $seasonId . '/teams/' . $teamId, [], $this->headers));
    }

    public function removeTeamFromSeason(string $seasonId, string $teamId): void
    {
        $this->handleErrors($this->slimClient->delete('/api/seasons/' . $seasonId . '/teams/' . $teamId));
    }

    public function getSeasonRanking(string $seasonId): stdClass
    {
        return $this->decodeBody($this->slimClient->get('/api/seasons/' . $seasonId . '/ranking'));
    }

    public function createTeam(string $name): stdClass
    {
        return $this->decodeBody($this->slimClient->post('/api/teams', ['name' => $name], $this->headers));
    }

    public function createMatches(string $seasonId, array $matchDays): void
    {
        $this->handleErrors($this->slimClient->post(
            '/api/seasons/' . $seasonId . '/match_days',
            ['dates' => $matchDays],
            $this->headers
        ));
    }

    public function getMatch(string $matchId): stdClass
    {
        return $this->decodeBody($this->slimClient->get('/api/matches/' . $matchId));
    }

    public function getMatchesByMatchDayId(string $matchDayId): array
    {
        return $this->decodeBody($this->slimClient->get(
            '/api/matches?' . http_build_query(['match_day_id' => $matchDayId])
        ));
    }

    public function getMatchesBySeasonId(string $seasonId): array
    {
        return $this->decodeBody($this->slimClient->get(
            '/api/matches?' . http_build_query(['season_id' => $seasonId])
        ));
    }

    public function submitMatchResult(string $matchId, int $homeScore, int $guestScore): void
    {
        $this->handleErrors($this->slimClient->post(
            '/api/matches/' . $matchId . '/result',
            ['home_score' => $homeScore, 'guest_score' => $guestScore],
            $this->headers
        ));
    }

    public function createTournament(string $name): stdClass
    {
        return $this->decodeBody($this->slimClient->post('/api/tournaments', ['name' => $name], $this->headers));
    }

    public function getMatchesInTournament(string $tournamentId): array
    {
        return $this->decodeBody($this->slimClient->get(
            '/api/matches?' . http_build_query(['tournament_id' => $tournamentId])
        ));
    }

    public function getAllTournaments(): array
    {
        return $this->decodeBody($this->slimClient->get('/api/tournaments'));
    }

    public function getTournament(string $id): stdClass
    {
        return $this->decodeBody($this->slimClient->get('/api/tournaments/' . $id));
    }

    public function setTournamentRound(string $tournamentId, int $round, array $teamPairs, array $datePeriod): void
    {
        $this->handleErrors($this->slimClient->put(
            '/api/tournaments/' . $tournamentId . '/rounds/' . $round,
            [
                'date_period' => $datePeriod,
                'team_pairs'  => $teamPairs
            ],
            $this->headers
        ));
    }

    public function getAuthenticatedUser(): stdClass
    {
        return $this->decodeBody($this->slimClient->get('/api/users/me', $this->headers));
    }

    public function changePassword(string $newPassword): void
    {
        $this->handleErrors($this->slimClient->put(
            '/api/users/me/password',
            ['new_password' => $newPassword],
            $this->headers
        ));
    }

    public function createUser(array $properties): stdClass
    {
        return $this->decodeBody($this->slimClient->post('/api/users', $properties, $this->headers));
    }

    public function createPitch($label, $latitude, $longitude): stdClass
    {
        return $this->decodeBody($this->slimClient->post(
            '/api/pitches',
            [
                'label' => $label,
                'location_latitude' => $latitude,
                'location_longitude' => $longitude
            ],
            $this->headers
        ));
    }

    public function locateMatch(string $matchId, string $pitchId)
    {
        $this->handleErrors($this->slimClient->post(
            '/api/matches/' . $matchId . '/location',
            [
                'pitch_id' => $pitchId
            ],
            $this->headers
        ));
    }

    public function scheduleMatch(string $matchId, string $kickoffDate)
    {
        $this->handleErrors($this->slimClient->post(
            '/api/matches/' . $matchId . '/kickoff',
            [
                'kickoff' => $kickoffDate
            ],
            $this->headers
        ));
    }

    public function updateTeamContact(string $teamId, array $contact)
    {
        $this->handleErrors($this->slimClient->put(
            '/api/teams/' . $teamId . '/contact',
            $contact,
            $this->headers
        ));
    }

    public function updatePitchContact(string $pitchId, array $contact)
    {
        $this->handleErrors($this->slimClient->put(
            '/api/pitches/' . $pitchId . '/contact',
            $contact,
            $this->headers
        ));
    }

    public function getTeam(string $teamId): stdClass
    {
        return $this->decodeBody($this->slimClient->get(
            '/api/teams/' . $teamId
        ));
    }

    public function getPitch(string $pitchId): stdClass
    {
        return $this->decodeBody($this->slimClient->get(
            '/api/pitches/' . $pitchId
        ));
    }

    public function rescheduleMatchDay(string $matchDayId, array $datePeriod): void
    {
        $this->handleErrors($this->slimClient->patch(
            '/api/match_days/' . $matchDayId,
            ['date_period' => $datePeriod],
            $this->headers
        ));
    }

    public function getMatchDaysForSeason(string $seasonId): array
    {
        return $this->decodeBody($this->slimClient->get(
            '/api/seasons/' . $seasonId. '/match_days'
        ));
    }

    public function deleteTournament(string $tournamentId): void
    {
        $this->handleErrors($this->slimClient->delete(
            '/api/tournaments/' . $tournamentId,
            $this->headers
        ));
    }

    public function deleteUser(string $userId): void
    {
        $this->handleErrors($this->slimClient->delete(
            '/api/users/' . $userId,
            $this->headers
        ));
    }

    public function getAllUsers(): array
    {
        return $this->decodeBody($this->slimClient->get(
            '/api/users',
            $this->headers
        ));
    }

    public function sendPasswordResetMail(string $email, string $targetPath): void
    {
        $this->handleErrors($this->slimClient->post(
            '/api/users/me/password/reset',
            ['email' => $email, 'target_path' => $targetPath]
        ));
    }

    public function endSeason(string $seasonId): void
    {
        $this->handleErrors($this->slimClient->post(
            '/api/seasons/' . $seasonId . '/end',
            [],
            $this->headers
        ));
    }

    private function decodeBody(ResponseInterface $response)
    {
        $this->handleErrors($response);
        return $this->slimClient->parseBody($response->getBody());
    }

    private function handleErrors(ResponseInterface $response): void
    {
        if ($response->getStatusCode() >= 400) {
            $body    = $this->slimClient->parseBody($response->getBody());
            $message = isset($body->message) ? $body->message : $response->getReasonPhrase();
            throw new ApiException($message, $response->getStatusCode());
        }
    }
}