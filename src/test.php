<?php
$container = require __DIR__ . '/bootstrap.php';

/** @var \Doctrine\ORM\EntityManager $entityManager */
$entityManager = $container['doctrine.entityManager'];
$uuidGenerator = $container['framework.persistence.uuidGenerator'];
$readDbAdapter = $container['framework.persistence.pdoReadDbAdapter'];
$seasonRepo = new \HexagonalDream\Application\Repository\SeasonRepository($readDbAdapter);
$teamRepo = new \HexagonalDream\Application\Repository\TeamRepository($readDbAdapter);
$seasons = $seasonRepo->getAllSeasons();
$teams = $teamRepo->getAllTeams();
var_dump($seasons);
var_dump($teams);
exit;

$season = new \HexagonalDream\Domain\Season();

if (($handle = fopen(__DIR__ . "/../data/teams.csv", "r")) !== false) {
    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
        list($teamName) = $data;
        $season->addTeam(new \HexagonalDream\Domain\Team($uuidGenerator, $teamName));
    }
    fclose($handle);
}

$command = new \HexagonalDream\Application\Command\CreateMatchDaysCommand($season, $season->getTeams());
$handler = new \HexagonalDream\Application\Handler\CreateMatchDaysHandler($uuidGenerator);
$matchDays = $handler->handle($command);
foreach ($matchDays as $matchDay) {
    echo $matchDay->toString() . PHP_EOL;
}