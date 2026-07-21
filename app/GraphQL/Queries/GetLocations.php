<?php

namespace App\GraphQL\Queries;

class GetLocations
{
    public const QUERY = <<<'GRAPHQL'
query GetLocations($first: Int!) {
  locations(first: $first) {
    nodes {
      id
      name
      address {
        address1
        address2
        city
        province
        country
        zip
      }
      isActive
    }
  }
}
GRAPHQL;
}