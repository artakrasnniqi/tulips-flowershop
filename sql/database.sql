CREATE DATABASE IF NOT EXISTS tulips_flowershop;
USE tulips_flowershop;

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) NOT NULL,
    stock INT NOT NULL DEFAULT 20,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(80) NULL UNIQUE,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NULL,
    address TEXT NOT NULL,
    city VARCHAR(120) NULL,
    phone VARCHAR(50) NOT NULL,
    delivery_option VARCHAR(100) NOT NULL DEFAULT 'standard',
    payment_method VARCHAR(100) NOT NULL,
    payment_status VARCHAR(50) NOT NULL DEFAULT 'pending',
    notes TEXT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    order_status VARCHAR(50) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

INSERT INTO products (name, category, description, price, image) VALUES
('Pink Peony Luxury', 'Bouquets', 'Elegant bouquet of premium pink peony-shaped roses wrapped in luxury white paper.', 64.99, 'assets/img/0019_Peony-shaped_Rose_9.jpg'),
('White Eustoma Harmony', 'Bouquets', 'Fresh bouquet of white eustoma flowers in natural kraft wrapping.', 34.99, 'assets/img/0000_Bouquet_of_5_eustoms_in_craft.jpg'),
('Pink Bush Rose Bouquet', 'Bouquets', 'Soft pink peony-shaped bush roses arranged in a romantic floral bouquet.', 54.99, 'assets/img/0001_Bouquet_of_11_peony-shaped_bush_roses.jpg'),
('Lydia Pink Roses', 'Bouquets', 'Beautiful bouquet of Lydia pink roses wrapped elegantly for special occasions.', 42.99, 'assets/img/0002_Bouquet_Lydia.jpg'),
('White Rose Elegance', 'Bouquets', 'Classic bouquet of 19 white roses in designer packaging.', 59.99, 'assets/img/0003_Bouquet_of_19_white_roses_in_a_designer_package.jpg'),

('Airy Pink Peonies', 'Bouquets', 'Soft pink peonies wrapped in elegant pastel floral paper for luxury gifting.', 74.99, 'assets/img/0020_Bouquet_of_Airy_Peonies.jpg'),
('Avalanche White Roses', 'Bouquets', 'Premium bouquet of 51 white Avalanche roses in luxury designer wrapping.', 119.99, 'assets/img/0021_Bouquet_of_51_white_Avalanche_roses_in_designer_pa.jpg'),
('Classic Red Roses', 'Bouquets', 'Elegant bouquet of 21 long stem red roses perfect for romantic occasions.', 69.99, 'assets/img/0022_21_Red_rose_60_cm.jpg'),
('Peony Eucalyptus Harmony', 'Bouquets', 'Luxury bouquet of peony-shaped roses combined with fresh eucalyptus.', 84.99, 'assets/img/0023_Bouquet_Of_Peony-Shaped_Eucalyptus_Bush_Roses.jpg'),
('Premium Red Roses', 'Bouquets', 'Beautiful arrangement of 15 premium red roses wrapped elegantly.', 59.99, 'assets/img/0024_15_Premium_Red_Roses_60_cm.jpg'),

('Pure White Roses', 'Bouquets', 'Classic bouquet of 15 fresh white roses in minimalist wrapping.', 54.99, 'assets/img/0025_15_white_roses.jpg'),
('Into The Heart Roses', 'Bouquets', 'Romantic bouquet of pink peony-shaped roses designed with luxury floral wrapping.', 79.99, 'assets/img/0026_Bouquet_of_peony-shaped_roses_Into_the_heart_to_de.jpg'),
('French Roses & Eucalyptus', 'Bouquets', 'Elegant French roses with eucalyptus greenery for premium floral gifts.', 82.99, 'assets/img/0027_French_roses_with_eucalyptus_9.jpg'),
('Fragrant Red Roses', 'Bouquets', 'Fresh bouquet of 15 fragrant red roses for romantic celebrations.', 58.99, 'assets/img/0028_15_red_fragrant_roses.jpg'),
('Soft Pink Roses Deluxe', 'Bouquets', 'Luxury bouquet of 25 soft pink roses wrapped in premium white paper.', 88.99, 'assets/img/0029_25_soft_pink_roses.jpg'),

('White Chrysanthemum Bouquet', 'Bouquets', 'Elegant bouquet of white bush chrysanthemums with eucalyptus accents.', 52.99, 'assets/img/0030_Delicate_bouquet_of_white_bush_chrysanthemum_Newto.jpg'),
('Sunny Sunflowers', 'Bouquets', 'Bright bouquet of fresh sunflowers bringing warmth and positivity.', 46.99, 'assets/img/0031_Bouquet_of_sunny_Sunflowers.jpg'),
('Touching Alstroemeria', 'Bouquets', 'Romantic bouquet of 19 Alstroemeria flowers in pastel floral tones.', 57.99, 'assets/img/0032_TOP_PRICE_Touching_confession_from_19_Alstroemeria.jpg'),
('Pink Lily Elegance', 'Bouquets', 'Fresh bouquet of pink lilies designed for elegant celebrations.', 72.99, 'assets/img/0033_Bouquet_of_Pink_Lilies.jpg'),
('Duchess Garden Roses', 'Bouquets', 'Luxury bouquet of creamy garden roses wrapped in premium kraft paper.', 94.99, 'assets/img/0034_Duchess_.jpg'),

('Romantic Red Rose Collection', 'Bouquets', 'Classic bouquet of 25 romantic red roses in elegant packaging.', 89.99, 'assets/img/0035_25_red_roses.jpg'),
('French Rose Compliment', 'Bouquets', 'Soft bouquet of white French roses and eucalyptus greenery.', 76.99, 'assets/img/0036_Delicate_bouquet_compliments_white_French_rose_and.jpg'),
('Luxury White Roses 51', 'Bouquets', 'Large premium bouquet of 51 luxury white roses for unforgettable moments.', 129.99, 'assets/img/0037_Beautiful_bouquet_of_51_white_roses.jpg'),
('Ecuadorian Red Roses', 'Bouquets', 'Premium bouquet of 11 Ecuadorian red roses in designer packaging.', 63.99, 'assets/img/0038_11_red_Ecuadorian_roses_in_designer_packaging.jpg'),
('Pink Peonies Premium', 'Bouquets', 'Elegant bouquet of fresh pink peonies wrapped in soft pastel floral paper.', 92.99, 'assets/img/0039_Pink_peonies.jpg'),

('Golden Sunflower Bliss', 'Bouquets', 'Bright sunflower bouquet with decorative wheat accents for cheerful occasions.', 49.99, 'assets/img/0040_Bouquet_of_sunflowers.jpg'),
('Pink Peony Roses', 'Bouquets', 'Romantic bouquet of pink peony-shaped roses in soft pastel wrapping.', 74.99, 'assets/img/0041_Bouquet_of_bush_peony_roses_7_pcs.jpg'),
('Misty Bubble Roses', 'Bouquets', 'Elegant bouquet of Misty Bubble peony-shaped roses for luxury gifting.', 79.99, 'assets/img/0042_Peony-shaped_bush_Rose_Misty_Bubbles.jpg'),
('French Rose Elegance', 'Bouquets', 'Premium bouquet of delicate French roses wrapped beautifully.', 68.99, 'assets/img/0043_French_roses.jpg'),
('White Chrysanthemum Dream', 'Bouquets', 'Soft bouquet of white chrysanthemums in luxury pink wrapping.', 54.99, 'assets/img/0044___7_.jpg'),

('Pink Hydrangea Collection', 'Bouquets', 'Fresh bouquet of delicate pink hydrangeas for elegant celebrations.', 72.99, 'assets/img/0045_Delicate_pink_bouquet_of_hydrangeas.jpg'),
('Chamomile Bloom', 'Bouquets', 'Natural bouquet of fresh white chamomile flowers with rustic wrapping.', 44.99, 'assets/img/0046_Daisies_in_a_bouquet.jpg'),
('Pink Garden Roses', 'Bouquets', 'Romantic arrangement of pink garden roses in pastel floral paper.', 76.99, 'assets/img/0047___.jpg'),
('White Gladiolus Grace', 'Bouquets', 'Elegant bouquet of white gladiolus flowers wrapped in luxury paper.', 58.99, 'assets/img/0048__.jpg'),
('101 Red Roses Deluxe', 'Bouquets', 'Large luxury bouquet of 101 premium red roses for unforgettable moments.', 249.99, 'assets/img/0049_101_Roses_for_your_beloved.jpg'),

('Chamomile Garden Bouquet', 'Bouquets', 'Fresh bouquet of chamomile flowers designed for soft natural elegance.', 46.99, 'assets/img/0050_Bouquet_of_Chamomile.jpg'),
('31 Pink Roses Luxury', 'Bouquets', 'Designer bouquet of 31 pink peony-shaped roses in premium packaging.', 114.99, 'assets/img/0051_31_peony-shaped_roses_in_designer_packaging.jpg'),
('Peony Compliment Bouquet', 'Bouquets', 'Elegant bouquet of soft pink peonies with luxury white wrapping.', 89.99, 'assets/img/0052_Peonies_are_a_compliment.jpg'),
('Lavender Dahlia Bouquet', 'Bouquets', 'Luxury bouquet of soft lavender dahlias wrapped elegantly.', 67.99, 'assets/img/0053___11_.jpg'),
('French Roses & Eucalyptus Pink', 'Bouquets', 'Delicate bouquet of French roses combined with fresh eucalyptus.', 82.99, 'assets/img/0054_A_delicate_pink_bouquet_of_French_Roses_with_eucal.jpg'),

('Pink Dianthus Bouquet', 'Bouquets', 'Soft bouquet of pink dianthus flowers with eucalyptus greenery.', 63.99, 'assets/img/0055_Bouquet_of_Dianthus_and_eucalyptus.jpg'),
('25 Premium Red Roses', 'Bouquets', 'Elegant bouquet of 25 premium long stem red roses.', 99.99, 'assets/img/0056_25_Red_premium_Roses_60cm.jpg'),
('Peony Romance', 'Bouquets', 'Fresh bouquet of 7 luxury peonies wrapped in premium floral paper.', 69.99, 'assets/img/0057_A_gorgeous_bouquet_of_7_peonies.jpg'),
('Prince Pink Roses', 'Bouquets', 'Luxury bouquet of peony-shaped bush roses in romantic pink shades.', 78.99, 'assets/img/0058_Delicate_bouquet_of_peony-shaped_bush_roses_Prince.jpg'),
('Royal Red Roses', 'Bouquets', 'Luxury bouquet of premium 51 red roses wrapped in elegant kraft paper.', 129.99, 'assets/img/0059_Red_roses_51_pieces.jpg'),

('Wonderful Morning Roses', 'Bouquets', 'Lush bouquet of soft blush bush roses with eucalyptus leaves.', 74.99, 'assets/img/0061_Lush_bouquet_of_bush_roses_-_Wonderful_morning.jpg'),
('Pink Mondial Deluxe', 'Bouquets', 'Elegant bouquet of unusual French Pink Mondial roses wrapped luxuriously.', 84.99, 'assets/img/0062_A_bouquet_of_delicate_unusual_French_Pink_Mondial_.jpg'),
('Pink Peony Craft', 'Bouquets', 'Fresh bouquet of 11 delicate pink peonies in natural kraft wrapping.', 89.99, 'assets/img/0063_Bouquet_of_11_peonies_in_craft.jpg'),
('Blue Cornflower Bloom', 'Bouquets', 'Bright bouquet of blue field cornflowers in elegant blue wrapping.', 49.99, 'assets/img/0064_Cornflowers._Bouquet_of_field_Cornflowers.jpg'),
('Lavender Bubble Roses', 'Bouquets', 'Luxury bouquet of lavender peony-shaped roses with soft pastel wrapping.', 79.99, 'assets/img/0065_Peony-shaped_Lavender_bubbles_roses.jpg'),

('French Pink Mondial', 'Bouquets', 'Premium bouquet of 7 French Pink Mondial roses with elegant wrapping.', 69.99, 'assets/img/0066_NEW_French_Rose_Pink_Mondial_7.jpg'),
('Pink Garden Rose Collection', 'Bouquets', 'Romantic bouquet of luxury pink garden roses for special moments.', 119.99, 'assets/img/0067______15_.jpg'),
('25 Red Rose Passion', 'Bouquets', 'Classic bouquet of 25 fresh red roses wrapped beautifully.', 94.99, 'assets/img/0068_25_red_roses.jpg'),
('Leora Fragrant Roses', 'Bouquets', 'Luxury bouquet of 25 fragrant pink roses in elegant floral paper.', 109.99, 'assets/img/0069_Bouquet._25_Fragrant_roses._Monobucket_557._Leora_.jpg'),
('11 White Rose Elegance', 'Bouquets', 'Elegant bouquet of 11 premium white roses with luxury wrapping.', 64.99, 'assets/img/0070_11_white_roses.jpg'),

('Rose of France', 'Bouquets', 'Luxury bouquet of delicate pink French roses with romantic design.', 79.99, 'assets/img/0071_Rose_of_France.jpg'),
('7 French Roses', 'Bouquets', 'Soft bouquet of 7 delicate French roses with premium wrapping.', 59.99, 'assets/img/0072_7_delicate_French_roses.jpg'),
('Red French Roses', 'Bouquets', 'Bouquet of 9 red French roses combined with fresh eucalyptus.', 74.99, 'assets/img/0073_Bouquet_of_9_Red_French_Roses_and_eucalyptus._Bouq.jpg'),
('Avalanche White Roses 25', 'Bouquets', 'Luxury bouquet of 25 Avalanche white roses wrapped elegantly.', 114.99, 'assets/img/0074_Avalanche_25.jpg'),
('Peach Sorbet Roses', 'Bouquets', 'Beautiful bouquet of peach sorbet peony roses in designer wrapping.', 84.99, 'assets/img/0075_Peach_sorbet_with_peony_roses_madame_bombastic_9.jpg'),

('Soft Pink Carnations', 'Bouquets', 'Elegant bouquet of soft pink carnations with luxury floral paper.', 58.99, 'assets/img/0076_____.jpg'),
('25 Bush Roses Craft', 'Bouquets', 'Premium bouquet of 25 pink bush roses in kraft packaging.', 96.99, 'assets/img/0077_25_bush_roses_in_a_craft_package.jpg'),
('Lavender Hydrangeas', 'Bouquets', 'Soft lavender hydrangea bouquet wrapped in elegant purple paper.', 74.99, 'assets/img/0078_Bouquet_of_lavender_hydrangeas.jpg'),
('Peony-Shaped Roses', 'Bouquets', 'Romantic bouquet of peony-shaped roses in pastel floral wrapping.', 78.99, 'assets/img/0079_Bouquet_of_peony-shaped_roses.jpg'),
('Red Roses 25 Pieces', 'Bouquets', 'Classic bouquet of 25 red roses wrapped beautifully for romantic gifts.', 94.99, 'assets/img/0080_Red_Roses_25_pieces.jpg'),

('Peony Bubbles Roses', 'Bouquets', 'Bouquet of 11 Peony Bubbles bush roses with delicate pink tones.', 69.99, 'assets/img/0081_11_Peony_Bush_Roses_Peony_Bubbles.jpg'),
('Space White Orchids', 'Bouquets', 'Elegant bouquet of white orchids in luxury floral packaging.', 84.99, 'assets/img/0082_Bouquet_of_orchids._Space_in_white._Monobucket_116.jpg'),
('Country Blues Roses', 'Bouquets', 'Bouquet of 7 peony-shaped Country Blues roses with romantic style.', 67.99, 'assets/img/0083_Bouquet_of_7_peony-shaped_roses_Country_Blues.jpg'),
('Soft Pink Dianthus', 'Bouquets', 'Delicate bouquet of 15 soft pink dianthus flowers.', 54.99, 'assets/img/0084_15_soft_pink_dianthuses.jpg'),
('French Roses Classic', 'Bouquets', 'Classic bouquet of French roses with premium floral wrapping.', 76.99, 'assets/img/0085_French_roses.jpg'),

('Red Rose Luxury', 'Bouquets', 'Luxury red rose bouquet wrapped in elegant black paper.', 82.99, 'assets/img/0086_.jpg'),
('Elite Roses 51', 'Bouquets', 'Large luxury bouquet of 51 elite roses for unforgettable moments.', 139.99, 'assets/img/0087_51_elite_Roses.jpg'),
('Pink Mondial French Roses', 'Bouquets', 'Bouquet of pink French roses Pink Mondial with elegant wrapping.', 79.99, 'assets/img/0088_Bouquet_of_pink_French_roses_Pink_Mondial.jpg'),
('Alstroemeria Large Bouquet', 'Bouquets', 'Large colorful bouquet with fresh alstroemeria flowers.', 68.99, 'assets/img/0089_A_large_bouquet_with_alstroemeria.jpg'),
('Red Rose Bouquet 25', 'Bouquets', 'Premium bouquet of 25 red roses in modern red wrapping.', 99.99, 'assets/img/0090_Bouquet_of_25_red_roses.jpg'),

('Snow White Roses 31', 'Bouquets', 'Beautiful bouquet of 31 snow-white roses with elegant wrapping.', 109.99, 'assets/img/0091_Beautiful_bouquet_of_31_snow-white_roses.jpg'),
('Red Roses 21pcs', 'Bouquets', 'Romantic bouquet of 21 red roses with long stems.', 84.99, 'assets/img/0092_Red_Roses_21pcs_70cm.jpg'),
('Peony Rose Special', 'Bouquets', 'Special bouquet of 9 peony-shaped roses in soft luxury wrapping.', 64.99, 'assets/img/0093_shock_priceRose_peony-shaped_9.jpg'),
('White Chrysanthemum Compliment', 'Bouquets', 'Cute bouquet compliment with white bush chrysanthemums and eucalyptus.', 52.99, 'assets/img/0094_Cute_bouquet_compliment_bush_chrysanthemum_and_euc.jpg'),
('Hit Pink Roses', 'Bouquets', 'Beautiful bouquet of 21 pink roses wrapped in soft white paper.', 79.99, 'assets/img/0095_HitBouquet_of_21_pink_roses.jpg'),

('Full Monty French Roses', 'Bouquets', 'Elegant bouquet of 9 French roses with a soft romantic look.', 72.99, 'assets/img/0096_Bouquet_of_French_roses_Full_Monty_9_pieces.jpg'),
('Premium Rose Bouquet', 'Bouquets', 'Luxury bouquet of premium roses designed for special occasions.', 89.99, 'assets/img/0097_Bouquet_of_premium_roses.jpg'),
('Fresh Daisy Bouquet', 'Bouquets', 'Simple and natural bouquet of fresh white daisies in kraft wrapping.', 39.99, 'assets/img/0098_Bouquet_of_daisies.jpg'),
('White Bush Chrysanthemums', 'Bouquets', 'Large bouquet of white bush chrysanthemums with eucalyptus greenery.', 56.99, 'assets/img/0099_Bush_chrysanthemums_in_a_bouquet.jpg'),
('Chamomile Minimal Bouquet', 'Bouquets', 'Minimal bouquet of chamomile flowers wrapped in clean white paper.', 34.99, 'assets/img/0100___.jpg'),

('Eustoma Mix Bouquet', 'Bouquets', 'Designer bouquet of 15 mixed eustoma flowers in purple and white tones.', 69.99, 'assets/img/0101_Bouquet_of_15_eustoma_mix_in_a_designer_package_Bl.jpg'),
('Pink French Roses Bouquet', 'Bouquets', 'Luxury bouquet of soft pink French roses in elegant pastel wrapping.', 82.99, 'assets/img/0102_Bouquet_of_pink_French_roses.jpg'),
('Delicious Pink Rose', 'Bouquets', 'Romantic bouquet of large pink roses with a soft feminine style.', 79.99, 'assets/img/0103_Delicious_pink_rose.jpg'),
('Red Dahlia Viburnum Bouquet', 'Bouquets', 'Unique bouquet with red dahlias, viburnum berries, and fresh greenery.', 74.99, 'assets/img/0104_Dahlias_in_the_bouquet_Viburnum_red.jpg'),
('Peony-Shaped Lily Trio', 'Bouquets', 'Elegant bouquet of peony-shaped lilies in soft pink tones.', 78.99, 'assets/img/0105_Bouquet_of_A_Trio_Of_Peony-shaped_lilies.jpg'),

('Red Roses 25 Luxury', 'Bouquets', 'Classic bouquet of 25 red roses in romantic luxury wrapping.', 99.99, 'assets/img/0106_Red_Roses_25_pieces.jpg'),
('Cosmic Orchid Bouquet', 'Bouquets', 'Bright bouquet of cosmic dendrobium orchids in blue and purple tones.', 89.99, 'assets/img/0107_Bouquet_of_cosmic_orchids_dendrobium.jpg'),
('Pink Peony Bouquet', 'Bouquets', 'Soft bouquet of pink peonies wrapped in white floral paper.', 84.99, 'assets/img/0108_____9_.jpg'),
('Lavender Dahlia Bouquet Premium', 'Bouquets', 'Delicate bouquet of lavender dahlias in premium white and purple wrapping.', 72.99, 'assets/img/0109____.jpg'),
('Marie-Claire Bouquet', 'Bouquets', 'Elegant mixed rose bouquet with soft pink and white tones.', 76.99, 'assets/img/0110_Bouquet_of_Marie-Claire_.jpg'),

('Purple Alstroemeria Bouquet', 'Bouquets', 'Colorful bouquet with purple and pink alstroemeria flowers.', 58.99, 'assets/img/0111_Purple_color.jpg'),
('Peony Roses & Eucalyptus', 'Bouquets', 'Romantic bouquet of peony-shaped bush roses with eucalyptus.', 83.99, 'assets/img/0112_Bouquet_of_Peony-Shaped_Bush_Roses_and_Eucalyptus.jpg'),
('Alstroemeria 15 Pieces', 'Bouquets', 'Fresh bouquet of 15 colorful alstroemeria flowers in kraft paper.', 54.99, 'assets/img/0113_15_alstroemeria.jpg'),
('Red French Roses Bouquet', 'Bouquets', 'Luxury bouquet of red French roses wrapped in white floral paper.', 86.99, 'assets/img/0114_Bouquet_of_red_French_roses.jpg'),
('White Gladiolus Bouquet', 'Bouquets', 'Elegant bouquet of tall white gladiolus flowers in luxury wrapping.', 62.99, 'assets/img/0115_Delicate_bouquet_of_white_gladiolus.jpg'),

('Pink Gladiolus Bouquet', 'Bouquets', 'Soft bouquet of pink gladiolus flowers for graceful occasions.', 62.99, 'assets/img/0116_Delicate_bouquet_of_pink_gladiolus.jpg'),
('Eustoma & Limonium Bouquet', 'Bouquets', 'Elegant monobouquet with white eustoma and purple limonium accents.', 64.99, 'assets/img/0117_Monobook_with_eustoma_and_limonium.jpg');
