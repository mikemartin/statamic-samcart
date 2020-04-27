<?php

namespace Mikemartin\Samcart\Tests;
use Illuminate\Http\Request;

class WebhookControllerTest  extends TestCase
{
    /** @test */
    public function can_store_products()
    {
        $body = $this->getRequestBody();

        $this
            ->actingAs($user)
            ->post(route('samcart.webhook'), $body)
            ->assertRedirect();

        $updatedEntry = Entry::find($entry->id());
        $this->assertStringContainsString($user->id(), json_encode($updatedEntry->value('likes')));
    }

}
