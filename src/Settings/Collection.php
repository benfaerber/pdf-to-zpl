<?php

namespace Faerber\PdfToZpl\Settings;

/** 
 * A very simple `Collection` class to avoid a dependency on `illuminate/collections`
 * This prevents being locked to a specific Laravel version or any framework.
 *
 * @template TKey of array-key
 * @template TValue
 */
class Collection {
    /** @var array<TKey, TValue> */
    private array $items; 
    
    /** @param array<TKey, TValue> $items */
    public function __construct(array $items = []) {
        $this->items = $items;
    }
    
    /** 
    * @param TValue[] $items 
    * @return Collection<TKey, TValue> 
    */
    public function from(array $items): self {
        return new Collection($items);
    }

    /** 
    * @param TValue[] $values 
    * @return Collection<TKey, TValue> 
    */
    public function push(...$values): self {
        foreach ($values as $value) {
            // I think it variadics might confuse phpstan... 
            /** @var TValue $value */
            $this->items[] = $value;
        }
        return $this;
    }

    /** 
    * @template TMapValue
    *
    * @param callable(TValue): TMapValue $callback
    * @return Collection<TKey, TMapValue>
    */
    public function map(callable $callback): self {
        return new Collection(array_map($callback, $this->items));
    }

    public function implode(string $seperator): string {
        return implode($seperator, $this->items);
    }

    /** @return array<TKey, TValue> */
    public function toArray(): array {
        return $this->items;
    }
}
