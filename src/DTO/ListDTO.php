<?php

namespace Core\DTO;

use Converter\DTO\ConverterDTO;
use Core\Interfaces\IListDTO;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ListDTO implements IListDTO {
    public function __construct(
        public readonly array $params,
    ) {}

    /**
     * @param Request $request
     * @param array $params = []
     * @param array $mapParams = []
     *
     * @return ListDTO
     */
    public static function fromRequest(Request $request, array $params = [], array $mapParams = []): ListDTO {
        $authUser = Auth::user();

        $convertDTO = ConverterDTO::getQueryParams(
            $request,
            [
                ...$params,
                'search', 'showDeleted', 'rowsPerPage', 'sortBy', 'archived', 'descending'
            ],
            $mapParams
        );

        return new self(
            params: [
                ...$convertDTO,
                'auth_user' => $authUser,
                'auth_user_id' => $authUser->id ?? null,
            ],
        );
    }

    /**
     * fromDefault
     *
     * @param array $params = []
     *
     * @return ListDTO
     */
    public static function fromDefault(array $params = []): ListDTO {
        return new self(
            params: $params
        );
    }

    /**
     * appendParams
     *
     * @param array $params
     *
     * @return ListDTO
     */
    public function appendParams(array $params): ListDTO
    {
        return new self(
            params: [
                ...$this->params,
                ...$params
            ]
        );
    }
}
