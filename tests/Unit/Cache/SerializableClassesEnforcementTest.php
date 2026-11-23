<?php

namespace Tests\Unit\Cache;

use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('smoke')]
class SerializableClassesEnforcementTest extends TestCase
{
    /**
     * F11 (spec 5.15 / D2): `serializable_classes => false` endurece la
     * deserializacion del store. El array store no serializa en runtime
     * normal, por eso el enforcement se verifica contra el file store, que
     * si serializa payloads.
     */
    public function test_f11_cached_objects_are_not_unserialized(): void
    {
        $store = Cache::store('file');
        $key = 'spec-5-15-f11-enforcement';

        try {
            $store->put($key, (object) ['x' => 1], 60);

            $value = $store->get($key);

            $this->assertNotInstanceOf(\stdClass::class, $value);
        } finally {
            $store->forget($key);
        }
    }
}
