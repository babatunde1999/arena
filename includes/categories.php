<?php
/**
 * Category helpers
 * Arena Market - E-Commerce Platform
 */

/**
 * Seed the categories table with the store's catalogue if it is empty.
 * Uses INSERT IGNORE so it is safe to call on every request.
 * @param PDO $db
 */
function ensureCategories($db) {
    $categories = [
        ['Shirts', 'shirts'],
        ['T-Shirts', 't-shirts'],
        ['Office Shirts', 'office-shirts'],
        ['Jeans', 'jeans'],
        ['Pant Trouser', 'pant-trouser'],
        ['Bags', 'bags'],
        ['Travelling Bag', 'travelling-bag'],
        ['Men Shoe', 'men-shoe'],
        ['Women Shoes', 'women-shoes'],
        ['Sneakers', 'sneakers'],
        ['Crocs', 'crocs'],
        ['Slide', 'slide'],
        ['Women Slide', 'women-slide'],
        ['Body Cream', 'body-cream'],
        ['Body Soap', 'body-soap'],
        ['Body Spray', 'body-spray'],
        ['Perfume', 'perfume'],
        ['Deodorant', 'deodorant'],
        ['Fridge', 'fridge'],
        ['JBL / Speakers', 'jbl-speakers'],
        ['Television', 'television'],
        ['Iron', 'iron'],
        ['Standing Fan', 'standing-fan'],
        ['Ceiling Fan', 'ceiling-fan'],
        ['Appliances', 'appliances'],
        ['Slippers', 'slippers'],
    ];

    $stmt = $db->prepare(
        "INSERT IGNORE INTO categories (name, slug, created_at) VALUES (:name, :slug, NOW())"
    );

    foreach ($categories as [$name, $slug]) {
        $stmt->execute([':name' => $name, ':slug' => $slug]);
    }
}