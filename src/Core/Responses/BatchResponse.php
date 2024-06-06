<?php

namespace Pranju\Bitrix24\Core\Responses;

use Pranju\Bitrix24\Contracts\Responses\BatchResponse as BatchResponseInterface;
use Pranju\Bitrix24\Contracts\Responses\Response as ResponseInterface;

class BatchResponse extends Response implements BatchResponseInterface
{
    protected array $responses;

    /**
     * @inheritDoc
     */
    public function responses(): array
    {
        if ($this->responses ??= []) {
            return $this->responses;
        }

        foreach ($this->getResponseKeys() as $key) {
            $this->responses[$key] = $this->getResponseByKey($key);
        }

        return $this->responses;
    }

    /**
     * Gets response keys
     *
     * @return int[]|string[]
     */
    protected function getResponseKeys(): array
    {
        return array_keys($this->result('result', []));
    }

    /**
     * Creates response by key
     *
     * @param string $key
     * @return ResponseInterface
     */
    protected function getResponseByKey(string $key): ResponseInterface
    {
        $response = [
            'result' => $this->result("result.$key", []),
            'time' => $this->result("result_time.$key", []),
            'error' => $this->result("result_error.$key"),
            'next' => $this->result("result_next.$key"),
            'total' => $this->result("result_total.$key"),
        ];

        $response = array_filter($response, fn($val) => !is_null($val));

        if (isset($response['result']['result_time'])) {
            return new BatchResponse($response);
        }

        if (isset($response['next']) || isset($response['total'])) {
            return new ListResponse($response);
        }

        return new Response($response);
    }
}