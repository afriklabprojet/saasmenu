<?php

// @formatter:off
/**
 * A helper file for Laravel models to provide autocomplete support.
 * This file should be committed to version control.
 */

namespace App\Models {

    use Illuminate\Database\Eloquent\Model;

    /**
     * App\Models\User
     *
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CustomerAddress> $addresses
     * @property-read int|null $addresses_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Wishlist> $wishlist
     * @property-read int|null $wishlist_count
     * @method \Illuminate\Database\Eloquent\Relations\HasMany|\App\Models\CustomerAddress addresses()
     * @method \Illuminate\Database\Eloquent\Relations\HasMany|\App\Models\Wishlist wishlist()
     * @mixin \Illuminate\Database\Eloquent\Model
     */
    class User extends Model {}

    /**
     * App\Models\CustomerAddress
     *
     * @property int $id
     * @property int $user_id
     * @property string $address_name
     * @property string $address
     * @property string $phone
     * @property bool $is_default
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\User $user
     * @method \Illuminate\Database\Eloquent\Relations\BelongsTo|\App\Models\User user()
     * @mixin \Illuminate\Database\Eloquent\Model
     */
    class CustomerAddress extends Model {}

    /**
     * App\Models\Wishlist
     *
     * @property int $id
     * @property int $user_id
     * @property int $item_id
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\User $user
     * @property-read \App\Models\Item $item
     * @method \Illuminate\Database\Eloquent\Relations\BelongsTo|\App\Models\User user()
     * @method \Illuminate\Database\Eloquent\Relations\BelongsTo|\App\Models\Item item()
     * @mixin \Illuminate\Database\Eloquent\Model
     */
    class Wishlist extends Model {}
}
