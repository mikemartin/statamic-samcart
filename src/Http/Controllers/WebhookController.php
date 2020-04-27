<?php

namespace Mikemartin\Samcart\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Facades\Entry;
use Statamic\Facades\User;

class WebhookController
{
    public function store(Request $request)
    {

        // Validate the user input
        $validatedData = $request->validate([
            'order.id' => 'required|numeric',
            'customer.email' => 'required|email',
            'customer.first_name' => 'nullable|string|max:250',
            'customer.last_name' => 'nullable|string|max:250',
            'product.id' => 'required|numeric'
        ]);

        $slug = $validatedData['order']['id'];
        $email = $validatedData['customer']['email'];

        // Find course with matching samcart product id
        $product = Entry::query()
            ->where('collection','courses')
            ->where('product_id', $validatedData['product']['id'])
            ->first();

        // Check if member email exists
        $member = User::findByEmail($email);


        if (!$member) {
            // Collect user data and subscribe to product
            $user = [
                'first_name' => $validatedData['customer']['first_name'],
                'last_name' => $validatedData['customer']['last_name'],
                'products' => [$product->id()],
            ];
            // Create user from customer email
            $this->createUser($user, $email);
        } else {
            // Subscribe existing user to product
            $products = $member->value('products') ?? [];
            $products = array_merge($products, [$product->id()]);

            $member->set('products', $products)
            ->save();
        }

        // Create Order entry from request object
        if (!Entry::findBySlug($slug, 'orders')) {
            $this->createOrder($request, $slug);
        }

        return;
    }

    protected function createUser(array $data, string $email)
    {
        return User::make()
            ->email($email)
            ->data($data)
            ->groups('members')
            ->save();
    }

    protected function createOrder(object $data, string $slug)
    {
        $data['title'] = 'Order #'.$slug;
        $date = now()->timestamp;

        return Entry::make()
            ->collection('orders')
            ->locale('default')
            ->data($data)
            ->slug($slug)
            ->date(now())
            ->set('updated_at', $date)
            ->save();
    }
}
