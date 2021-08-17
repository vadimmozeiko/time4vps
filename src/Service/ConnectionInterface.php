<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

interface ConnectionInterface
{
    public function encodeCredentials(Request $request): string;

    public function authorize(Request $request): void;

    public function getResources(string $path): array;

    public function postTask(string $path): void;

    public function postTaskWithArgs(string $path, array $args): void;

}