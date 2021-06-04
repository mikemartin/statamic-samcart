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
        $name = $validatedData['customer']['first_name'];
        $lastName = $validatedData['customer']['last_name'];

        if ($lastName) {
          $name .= ' '.$lastName;
        }

        // Find courses and books with matching samcart product id
        $products = Entry::query()
            ->where('collection','courses')
            ->where('collection','books')
            ->where('product_id', 'like', "%{$validatedData['product']['id']}%")
            ->get()
            ->map(function ($product) {
              return $product->id();
            })
            ->toArray();

        // Check if member email exists
        $member = User::findByEmail($email);

        if ($type == 'Order') {
          if (!$member) {
              // Collect user data and subscribe to product
              $user = [
                  'name' => $name,
                  'products' => $products,
              ];
              // Create user from customer email
              $this->createUser($user, $email);
          } else {
              // Subscribe existing user to product
              $memberProducts = $member->value('products') ?? [];
              $memberProducts = collect($memberProducts)->merge($products)->unique()->sort()->values()->toArray();

              $member->set('products', $memberProducts)
              ->save();
          }

          // Create Order model from request object
          if (resolve(config('samcart.model'))::where('order_number', $slug)->count() == 0) {
              $this->createOrder($request, $slug);
          }
        }

        // Remove products for refund, cancellations and delinquents
        if ($type == 'Refund' || $type == 'SubscriptionDelinquent' || $type == 'Cancel' ) {
          $memberProducts = $member->value('products') ?? [];
          // Unsubscribe existing user from product
          foreach($products as $product){
            $memberProducts = collect($memberProducts)->reject($product)->sort()->values()->toArray();
          }

          $member->set('products', $memberProducts)
          ->save();
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

        return resolve(config('samcart.model'))::create([
            'title' => $data['title'],
            'order_number' => $slug,
            'product' => $data['product'],
            'customer' => $data['customer'],
            'order' => $data['order'],
        ]);
    }
}
