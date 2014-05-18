<?php namespace Hostbase\Entity;


interface EntityFinder {

    public function search($query, $limit);
} 