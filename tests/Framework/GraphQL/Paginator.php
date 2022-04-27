<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL;

use Iterator;

class Paginator
{
    private AdvancedClient $client;

    public function __construct(AdvancedClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param Query $query
     * @param int $pageSize
     * @param Auth|null $auth
     * @return Iterator|array[]
     */
    public function paginate(Query $query, int $pageSize = 100, ?Auth $auth = null): Iterator
    {
        $offset = 0;
        do {
            $payload = $query
                ->argTypes(['pagination' => 'Pagination'])
                ->argValues(['pagination' => ['limit' => $pageSize, 'offset' => $offset]]);

            $response = $this->client->request($payload, $auth);

            if (isset($response->errors) && count($response->errors) > 0) {
                throw new Exception($response->errors);
            }

            if (!isset($response->data) || !is_object($response->data)) {
                throw new Exception(['Query response did not contain data']);
            }

            $result = current(get_object_vars($response->data));

            if (!is_array($result)) {
                throw new Exception(['Response does not contain data for pagination']);
            }

            if (count($result) > 0) {
                yield $result;
            }

            $offset += $pageSize;

        } while (count($result) > 0);
    }
}
