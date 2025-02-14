<?php

namespace Src\Application\User\Query;

final class SearchUserQuery
{
  public function __construct(
    private int $page = 1,
    private int $perPage = 15,
    private string $search = ''
  ) {}

  public function page(): int
  {
    return $this->page;
  }

  public function perPage(): int
  {
    return $this->perPage;
  }

  public function criteria(): array
  {
    if ($this->search) {
      return explode(' ', $this->search);
    }
    return [];
  }
}
