<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL;

use HexagonalPlayground\Tests\Framework\JsonResponseParser;
use HexagonalPlayground\Tests\Framework\SlimClient;
use Psr\Http\Message\ResponseInterface;
use stdClass;

class Client
{
    /** @var SlimClient */
    private $slimClient;

    /** @var array */
    private $headers;

    /** @var JsonResponseParser */
    private $parser;

    /**
     * @param SlimClient $slimClient
     */
    public function __construct(SlimClient $slimClient)
    {
        $this->slimClient = $slimClient;
        $this->headers    = [];
        $this->parser     = new JsonResponseParser();
    }

    public function useCredentials(string $email, string $password): void
    {
        $this->headers['Authorization'] = 'Basic ' . base64_encode($email . ':' . $password);
    }

    public function useToken(string $token): void
    {
        $this->headers['Authorization'] = 'Bearer ' . $token;
    }

    public function clearAuth(): void
    {
        unset($this->headers['Authorization']);
    }

    public function getAllSeasons(): array
    {
        $query = <<<GRAPHQL
    query allSeasons {
      allSeasons {
        id,
        name
      }
    }
GRAPHQL;
        $data = $this->requestAndParse($query);
        return $data->allSeasons;
    }

    public function createPitch($id, $label, $latitude, $longitude): void
    {
        $query = <<<'GRAPHQL'
mutation createPitch($id: String, $label: String!, $longitude: Float!, $latitude: Float!) {
  createPitch(id: $id, label: $label, longitude: $longitude, latitude: $latitude)
}
GRAPHQL;

        $this->requestAndParse($query, [
            'id'        => $id,
            'label'     => $label,
            'longitude' => $longitude,
            'latitude'  => $latitude
        ]);
    }

    public function getPitchById($id): ?stdClass
    {
        $query = <<<'GRAPHQL'
query pitch($id: String!) {
  pitch(id: $id) {
    id,
    label,
    location_latitude,
    location_longitude,
    contact {
      first_name,
      last_name,
      phone,
      email
    }
  }
}
GRAPHQL;

        $data = $this->requestAndParse($query, ['id' => $id]);
        return $data->pitch ?? null;
    }

    public function updatePitchContact($pitchId, $contact): void
    {
        $query = <<<'GRAPHQL'
mutation updatePitchContact($pitchId: String!, $firstName: String!, $lastName: String!, $phone: String!, $email: String!) {
  updatePitchContact(pitch_id: $pitchId, first_name: $firstName, last_name: $lastName, phone: $phone, email: $email)
}
GRAPHQL;

        $this->requestAndParse($query, [
            'pitchId' => $pitchId,
            'firstName' => $contact['first_name'],
            'lastName' => $contact['last_name'],
            'phone' => $contact['phone'],
            'email' => $contact['email']
        ]);
    }

    public function deletePitch($pitchId): void
    {
        $query = <<<'GRAPHQL'
mutation deletePitch($pitchId: String!) {
  deletePitch(pitch_id: $pitchId)
}
GRAPHQL;

        $this->requestAndParse($query, [
            'pitchId' => $pitchId
        ]);
    }

    public function createTeam($id, $name): void
    {
        $query = <<<'GRAPHQL'
mutation createTeam($id: String!, $name: String!) {
  createTeam(id: $id, name: $name)
}
GRAPHQL;

        $this->requestAndParse($query, [
            'id'   => $id,
            'name' => $name
        ]);
    }

    public function getTeamById($id): ?stdClass
    {
        $query = <<<'GRAPHQL'
query team($id: String!) {
  team(id: $id) {
    id,
    name,
    created_at,
    contact {
      first_name,
      last_name,
      phone,
      email
    }
  }
}
GRAPHQL;

        $data = $this->requestAndParse($query, ['id' => $id]);
        return $data->team ?? null;
    }

    public function renameTeam($teamId, $newName): void
    {
        $query = <<<'GRAPHQL'
mutation renameTeam($teamId: String!, $newName: String!) {
  renameTeam(team_id: $teamId, new_name: $newName)
}
GRAPHQL;

        $this->requestAndParse($query, [
            'teamId' => $teamId,
            'newName' => $newName
        ]);
    }

    public function updateTeamContact($teamId, $contact): void
    {
        $query = <<<'GRAPHQL'
mutation updateTeamContact($teamId: String!, $firstName: String!, $lastName: String!, $phone: String!, $email: String!) {
  updateTeamContact(team_id: $teamId, first_name: $firstName, last_name: $lastName, phone: $phone, email: $email)
}
GRAPHQL;

        $this->requestAndParse($query, [
            'teamId' => $teamId,
            'firstName' => $contact['first_name'],
            'lastName' => $contact['last_name'],
            'phone' => $contact['phone'],
            'email' => $contact['email']
        ]);
    }

    public function deleteTeam($teamId): void
    {
        $query = <<<'GRAPHQL'
mutation deleteTeam($teamId: String!) {
  deleteTeam(team_id: $teamId)
}
GRAPHQL;

        $this->requestAndParse($query, [
            'teamId' => $teamId
        ]);
    }

    public function getAllTeams(): array
    {
        $query = <<<GRAPHQL
    query allTeams {
      allTeams {
        id,
        name,
        created_at,
        contact {
          first_name,
          last_name,
          phone,
          email
        }
      }
    }
GRAPHQL;
        $data = $this->requestAndParse($query);
        return $data->allTeams;
    }

    public function createSeason($id, $name): void
    {
        $query = <<<'GRAPHQL'
mutation createSeason($id: String!, $name: String!) {
  createSeason(id: $id, name: $name)
}
GRAPHQL;

        $this->requestAndParse($query, [
            'id' => $id,
            'name' => $name
        ]);
    }

    public function getSeasonById($id): ?stdClass
    {
        $query = <<<'GRAPHQL'
query season($id: String!) {
  season(id: $id) {
    id,
    name,
    state,
    match_day_count,
    team_count,
    ranking {
      updated_at,
      positions {
        team {
          id,
          name
        },
        sort_index,
        number,
        matches,
        wins,
        draws,
        losses,
        scored_goals,
        conceded_goals
        points
      }
    }
  }
}
GRAPHQL;

        $data = $this->requestAndParse($query, ['id' => $id]);
        return $data->season ?? null;
    }

    public function getSeasonByIdWithMatchDays($id): ?stdClass
    {
        $query = <<<'GRAPHQL'
query season($id: String!) {
  season(id: $id) {
    id,
    name,
    state,
    match_day_count,
    team_count,
    ranking {
      updated_at,
      positions {
        team {
          id,
          name
        },
        sort_index,
        number,
        matches,
        wins,
        draws,
        losses,
        scored_goals,
        conceded_goals
        points
      }
    },
    match_days {
      id,
      number,
      start_date,
      end_date,
      matches {
        id
      }
    }
  }
}
GRAPHQL;

        $data = $this->requestAndParse($query, ['id' => $id]);
        return $data->season ?? null;
    }

    public function addTeamToSeason($seasonId, $teamId): void
    {
        $query = <<<'GRAPHQL'
mutation addTeamToSeason($seasonId: String!, $teamId: String!) {
  addTeamToSeason(season_id: $seasonId, team_id: $teamId)
}
GRAPHQL;

        $this->requestAndParse($query, [
            'teamId' => $teamId,
            'seasonId' => $seasonId
        ]);
    }

    public function removeTeamFromSeason($seasonId, $teamId): void
    {
        $query = <<<'GRAPHQL'
mutation removeTeamFromSeason($seasonId: String!, $teamId: String!) {
  removeTeamFromSeason(season_id: $seasonId, team_id: $teamId)
}
GRAPHQL;

        $this->requestAndParse($query, [
            'teamId' => $teamId,
            'seasonId' => $seasonId
        ]);
    }

    public function startSeason($seasonId): void
    {
        $query = <<<'GRAPHQL'
mutation startSeason($seasonId: String!) {
  startSeason(season_id: $seasonId)
}
GRAPHQL;

        $this->requestAndParse($query, [
            'seasonId' => $seasonId
        ]);
    }

    public function createMatchesForSeason($seasonId, $dates): void
    {
        $query = <<<'GRAPHQL'
mutation createMatchesForSeason($seasonId: String!, $dates: [DatePeriod]!) {
  createMatchesForSeason(season_id: $seasonId, dates: $dates)
}
GRAPHQL;

        $this->requestAndParse($query, [
            'seasonId' => $seasonId,
            'dates' => $dates
        ]);
    }

    public function createUser(array $user): void
    {
        $query = <<<'GRAPHQL'
mutation createUser($id: String, $email: String!, $password: String!, $first_name: String!, $last_name: String!, $role: String!, $team_ids: [String]!) {
  createUser(id: $id, email: $email, password: $password, first_name: $first_name, last_name: $last_name, role: $role, team_ids: $team_ids)
}
GRAPHQL;

        $this->requestAndParse($query, $user);
    }

    public function getMatchById($id): ?stdClass
    {
        $query = <<<'GRAPHQL'
query match($id: String!) {
  match(id: $id) {
    id,
    home_team {
      id,
      name
    }
    guest_team {
      id,
      name
    },
    kickoff,
    home_score,
    guest_score,
    cancelled_at,
    cancellation_reason
    pitch {
      id,
      label    
    }
  }
}
GRAPHQL;

        $data = $this->requestAndParse($query, ['id' => $id]);
        return $data->match;
    }

    public function submitMatchResult($matchId, $homeScore, $guestScore): void
    {
        $query = <<<'GRAPHQL'
mutation submitMatchResult($matchId: String!, $homeScore: Int!, $guestScore: Int!) {
  submitMatchResult(match_id: $matchId, home_score: $homeScore, guest_score: $guestScore)
}
GRAPHQL;

        $this->requestAndParse($query, [
            'matchId' => $matchId,
            'homeScore' => $homeScore,
            'guestScore' => $guestScore
        ]);
    }

    private function requestAndParse(string $query, array $variables = []): stdClass
    {
        $response = $this->request($query, $variables);

        $body = json_decode($response->getBody()->__toString());

        if (isset($body->errors) && is_array($body->errors) && count($body->errors) > 0) {
            throw new Exception($body->errors);
        }

        return $body->data;
    }

    private function request(string $query, array $variables = []): ResponseInterface
    {
        return $this->slimClient->post(
            '/api/graphql/',
            ['query' => $query, 'variables' => $variables],
            $this->headers
        );
    }

    public function cancelMatch($matchId, $reason): void
    {
        $query = <<<'GRAPHQL'
mutation cancelMatch($matchId: String!, $reason: String!) {
  cancelMatch(match_id: $matchId, reason: $reason)
}
GRAPHQL;

        $this->requestAndParse($query, [
            'matchId' => $matchId,
            'reason'  => $reason
        ]);
    }

    public function endSeason($seasonId): void
    {
        $query = <<<'GRAPHQL'
mutation endSeason($seasonId: String!) {
  endSeason(season_id: $seasonId)
}
GRAPHQL;

        $this->requestAndParse($query, [
            'seasonId' => $seasonId
        ]);
    }

    public function addRankingPenalty($id, $seasonId, $teamId, $reason, $points)
    {
        $query = <<<'GRAPHQL'
mutation addRankingPenalty($id: String, $seasonId: String!, $teamId: String!, $reason: String!, $points: Int!) {
  addRankingPenalty(id: $id, season_id: $seasonId, team_id: $teamId, reason: $reason, points: $points)
}
GRAPHQL;

        $this->requestAndParse($query, [
            'id' => $id,
            'seasonId' => $seasonId,
            'teamId' => $teamId,
            'reason' => $reason,
            'points' => $points
        ]);
    }

    public function removeRankingPenalty($rankingPenaltyId, $seasonId)
    {
        $query = <<<'GRAPHQL'
mutation removeRankingPenalty($rankingPenaltyId: String!, $seasonId: String!) {
  removeRankingPenalty(ranking_penalty_id: $rankingPenaltyId, season_id: $seasonId)
}
GRAPHQL;

        $this->requestAndParse($query, [
            'rankingPenaltyId' => $rankingPenaltyId,
            'seasonId' => $seasonId
        ]);
    }

    public function rescheduleMatchDay($matchDayId, $datePeriod)
    {
        $query = <<<'GRAPHQL'
mutation rescheduleMatchDay($matchDayId: String!, $datePeriod: DatePeriod!) {
  rescheduleMatchDay(match_day_id: $matchDayId, date_period: $datePeriod)
}
GRAPHQL;

        $this->requestAndParse($query, [
            'matchDayId' => $matchDayId,
            'datePeriod' => $datePeriod
        ]);
    }

    public function deleteSeason($seasonId)
    {
        $query = <<<'GRAPHQL'
mutation deleteSeason($seasonId: String!) {
  deleteSeason(season_id: $seasonId)
}
GRAPHQL;

        $this->requestAndParse($query, [
            'seasonId' => $seasonId
        ]);
    }

    public function createTournament($id, $name)
    {
        $query = <<<'GRAPHQL'
mutation createTournament($id: String, $name: String!) {
  createTournament(id: $id, name: $name)
}
GRAPHQL;

        $this->requestAndParse($query, [
            'id' => $id,
            'name' => $name
        ]);
    }

    public function getTournamentById($id): ?stdClass
    {
        $query = <<<'GRAPHQL'
query tournament($id: String!) {
  tournament(id: $id) {
    id,
    name
  }
}
GRAPHQL;

        $data = $this->requestAndParse($query, ['id' => $id]);
        return $data->tournament;
    }

    public function getTournamentByIdWithRounds($id): ?stdClass
    {
        $query = <<<'GRAPHQL'
query tournament($id: String!) {
  tournament(id: $id) {
    id,
    name,
    rounds {
      id,
      number,
      start_date,
      end_date,
      matches {
        id,
        home_team {
          id,
          name
        },
        guest_team {
          id,
          name
        },
        kickoff,
        home_score,
        guest_score,
        cancelled_at,
        cancellation_reason
      }
    }
  }
}
GRAPHQL;

        $data = $this->requestAndParse($query, ['id' => $id]);
        return $data->tournament;
    }

    public function getAllTournaments(): array
    {
        $query = <<<GRAPHQL
    query allTournaments {
      allTournaments {
        id,
        name
      }
    }
GRAPHQL;
        $data = $this->requestAndParse($query);
        return $data->allTournaments;
    }

    public function setTournamentRound($tournamentId, $round, $teamIdPairs, $datePeriod): void
    {
        $query = <<<'GRAPHQL'
mutation setTournamentRound($tournamentId: String!, $round: Int!, $teamIdPairs: [TeamIdPair]!, $datePeriod: DatePeriod!) {
  setTournamentRound(tournament_id: $tournamentId, round: $round, team_id_pairs: $teamIdPairs, date_period: $datePeriod)
}
GRAPHQL;

        $this->requestAndParse($query, [
            'tournamentId' => $tournamentId,
            'round' => $round,
            'teamIdPairs' => $teamIdPairs,
            'datePeriod' => $datePeriod
        ]);
    }

    public function deleteTournament($tournamentId): void
    {
        $query = <<<'GRAPHQL'
mutation deleteTournament($tournamentId: String!) {
  deleteTournament(tournament_id: $tournamentId)
}
GRAPHQL;

        $this->requestAndParse($query, [
            'tournamentId' => $tournamentId
        ]);
    }

    public function getAllUsers(): array
    {
        $query = <<<GRAPHQL
query allUsers {
  allUsers {
    id,
    email,
    role,
    first_name,
    last_name
  }
}
GRAPHQL;
        $data = $this->requestAndParse($query);
        return $data->allUsers;
    }

    public function createToken(): string
    {
        $query = <<<GRAPHQL
query authenticatedUser {
  authenticatedUser {
    id
  }
}
GRAPHQL;
        $response = $this->request($query);

        $token = $response->getHeader('X-Token')[0] ?? null;

        if (null === $token) {
            throw new Exception(['Could not create token']);
        }

        return $token;
    }

    public function getAuthenticatedUser(): stdClass
    {
        $query = <<<GRAPHQL
query authenticatedUser {
  authenticatedUser {
    id,
    email,
    role,
    first_name,
    last_name
  }
}
GRAPHQL;
        $data = $this->requestAndParse($query);
        return $data->authenticatedUser;
    }

    public function changeUserPassword($newPassword): void
    {
        $query = <<<'GRAPHQL'
mutation changeUserPassword($newPassword: String!) {
  changeUserPassword(new_password: $newPassword)
}
GRAPHQL;

        $this->requestAndParse($query, [
            'newPassword' => $newPassword
        ]);
    }

    public function updateUser(array $user): void
    {
        $query = <<<'GRAPHQL'
mutation updateUser($id: String!, $email: String, $first_name: String, $last_name: String, $role: String, $team_ids: [String]) {
  updateUser(user_id: $id, email: $email, first_name: $first_name, last_name: $last_name, role: $role, team_ids: $team_ids)
}
GRAPHQL;

        $this->requestAndParse($query, $user);
    }

    public function deleteUser($id): void
    {
        $query = <<<'GRAPHQL'
mutation deleteUser($id: String!) {
  deleteUser(user_id: $id)
}
GRAPHQL;

        $this->requestAndParse($query, [
            'id' => $id
        ]);
    }

    public function sendPasswordResetMail($email, $targetPath)
    {
        $query = <<<'GRAPHQL'
mutation sendPasswordResetMail($email: String!, $target_path: String!){
  sendPasswordResetMail(email: $email, target_path: $target_path)
}
GRAPHQL;

        $this->requestAndParse($query, [
            'email' => $email,
            'target_path' => $targetPath
        ]);
    }

    public function locateMatch($matchId, $pitchId): void
    {
        $query = <<<'GRAPHQL'
mutation locateMatch($match_id: String!, $pitch_id: String!) {
  locateMatch(match_id: $match_id, pitch_id: $pitch_id)
}
GRAPHQL;

        $this->requestAndParse($query, [
            'match_id' => $matchId,
            'pitch_id' => $pitchId
        ]);
    }

    public function sendInviteMail($userId, $targetPath): void
    {
        $query = <<<'GRAPHQL'
mutation sendInviteMail($user_id: String!, $target_path: String!) {
  sendInviteMail(user_id: $user_id, target_path: $target_path)
}
GRAPHQL;

        $this->requestAndParse($query, [
            'user_id' => $userId,
            'target_path' => $targetPath
        ]);
    }

    public function scheduleMatch($matchId, $kickoff): void
    {
        $query = <<<'GRAPHQL'
mutation scheduleMatch($match_id: String!, $kickoff: DateTime!) {
  scheduleMatch(match_id: $match_id, kickoff: $kickoff)
}
GRAPHQL;

        $this->requestAndParse($query, [
            'match_id' => $matchId,
            'kickoff' => $kickoff
        ]);
    }

    public function invalidateAccessTokens(): void
    {
        $query = <<<'GRAPHQL'
mutation invalidateAccessTokens{
  invalidateAccessTokens
}
GRAPHQL;

        $this->requestAndParse($query);
    }

    public function scheduleAllMatchesForSeason($seasonId, $matchAppointments): void
    {
        $query = <<<'GRAPHQL'
mutation scheduleAllMatchesForSeason($season_id: String!, $match_appointments: [MatchAppointment]!) {
  scheduleAllMatchesForSeason(season_id: $season_id, match_appointments: $match_appointments)
}
GRAPHQL;

        $this->requestAndParse($query, [
            'season_id' => $seasonId,
            'match_appointments' => $matchAppointments
        ]);
    }
}
