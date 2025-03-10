<?php

namespace Src\Infrastructure\Http\Transformers;

use League\Fractal\TransformerAbstract;

abstract class AbstractTransformer extends TransformerAbstract
{
  protected function formatDateTime(?\DateTimeInterface $dateTime): ?string
  {
    return $dateTime?->format('Y-m-d H:i:s');
  }
}
