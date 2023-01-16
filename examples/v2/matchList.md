# MatchList

Retrieve a filtered list of matches

## Query

```
query matchList ($filter: MatchFilter) {
  matchList (filter: $filter) {
    id
    matchDay {
      id
      number
      season {
        id
        name
      }
      tournament {
        id
        name
      }
    }
    homeTeam {
      id
      name
    }
    guestTeam {
      id
      name
    }
    kickoff
    result {
      homeScore
      guestScore
    }
    cancellation {
	  createdAt
      reason
    }
  }
}
```

## Variables

```json
{
  "filter": {
    "kickoffAfter": "2023-02-01",
    "kickoffBefore": "2023-02-28"
  }
}
```
