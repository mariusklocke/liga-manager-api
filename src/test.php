<?php
$container = require __DIR__ . '/bootstrap.php';

/** @var \Doctrine\ORM\EntityManager $entityManager */
//$entityManager = $container['doctrine.entityManager'];
$uuidGenerator = $container['infrastructure.persistence.uuidGenerator'];
//$readDbAdapter = $container['infrastructure.persistence.pdoReadDbAdapter'];
/*$seasonRepo = new \HexagonalDream\Application\Repository\SeasonRepository($readDbAdapter);
$teamRepo = new \HexagonalDream\Application\Repository\TeamRepository($readDbAdapter);
$seasons = $seasonRepo->getAllSeasons();
$teams = $teamRepo->getAllTeams();
var_dump($seasons);
var_dump($teams);
exit;*/

$season = new \HexagonalDream\Domain\Season($uuidGenerator, 'Test');
if (($handle = fopen(__DIR__ . "/../data/teams.csv", "r")) !== false) {
    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
        list($teamName) = $data;
        $season->addTeam(new \HexagonalDream\Domain\Team($uuidGenerator, $teamName));
    }
    fclose($handle);
}

//$command = new \HexagonalDream\Application\Command\CreateMatchDaysCommand($season, $season->getTeams());
$handler = new \HexagonalDream\Application\Handler\CreateMatchDaysHandler($uuidGenerator);
$matchDays = $handler->createMatchDays($season);

$ranking = new \HexagonalDream\Domain\Ranking($season);
$match = $matchDays[0]->getMatches()[0]->submitResult(new \HexagonalDream\Domain\MatchResult(4,1));
$ranking->addResult($match);
$match = $matchDays[0]->getMatches()[1]->submitResult(new \HexagonalDream\Domain\MatchResult(4,5));
$ranking->addResult($match);
$match = $matchDays[0]->getMatches()[2]->submitResult(new \HexagonalDream\Domain\MatchResult(5,5));
$ranking->addResult($match);
