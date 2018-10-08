
CREATE TABLE match_days (id VARCHAR(255) NOT NULL, season_id VARCHAR(255) DEFAULT NULL, tournament_id VARCHAR(255) DEFAULT NULL, number INT NOT NULL, start_date DATE NOT NULL COMMENT '(DC2Type:date_immutable)', end_date DATE NOT NULL COMMENT '(DC2Type:date_immutable)', INDEX IDX_8A8B4D144EC001D1 (season_id), INDEX IDX_8A8B4D1433D1A3E7 (tournament_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE match_days ADD CONSTRAINT FK_8A8B4D144EC001D1 FOREIGN KEY (season_id) REFERENCES seasons (id);
ALTER TABLE match_days ADD CONSTRAINT FK_8A8B4D1433D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournaments (id);

INSERT INTO match_days (id, season_id, tournament_id, number, start_date, end_date)
SELECT UUID(), season_id, tournament_id, match_day, planned_for, planned_for
FROM matches GROUP BY season_id, tournament_id, match_day;

ALTER TABLE matches ADD match_day_id VARCHAR(255) NOT NULL;

UPDATE matches m
INNER JOIN match_days md ON m.match_day = md.number AND m.season_id = md.season_id
SET m.match_day_id = md.id
WHERE m.tournament_id IS NULL;

UPDATE matches m
INNER JOIN match_days md ON m.match_day = md.number AND m.tournament_id = md.tournament_id
SET m.match_day_id = md.id
WHERE m.season_id IS NULL;

ALTER TABLE matches ADD CONSTRAINT FK_62615BAA8ADB827 FOREIGN KEY (match_day_id) REFERENCES match_days (id);
CREATE INDEX IDX_62615BAA8ADB827 ON matches (match_day_id);

ALTER TABLE matches DROP FOREIGN KEY FK_62615BA33D1A3E7;
ALTER TABLE matches DROP FOREIGN KEY FK_62615BA4EC001D1;
DROP INDEX IDX_62615BA4EC001D1 ON matches;
DROP INDEX IDX_62615BA33D1A3E7 ON matches;
DROP INDEX idx_season_match_day ON matches;
ALTER TABLE matches DROP season_id, DROP tournament_id, DROP match_day;
