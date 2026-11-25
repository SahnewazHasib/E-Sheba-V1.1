with open(r"C:\xampp\htdocs\e-sheba\categories.php", "r", encoding="utf-8") as f:
    c = f.read()

# Replace Brand text with SVG logo
c = c.replace(
    '<a href="index.php" class="brand text-decoration-none">🔧 <?= APP_NAME ?></a>',
    '<a href="index.php" class="brand text-decoration-none py-1"><img src="images/logo.svg" alt="E-Sheba Logo" class="brand-logo-img"></a>'
)

if "https://unpkg.com/aos" not in c:
    c = c.replace(
        '<link rel="stylesheet" href="css/style.css">',
        '<link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">\n  <link rel="stylesheet" href="css/style.css">'
    )
    c = c.replace(
        '</body>',
        '<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>\n<script>AOS.init({duration:600,once:true});</script>\n</body>'
    )

old_cat_box = """    <?php foreach ($categories as $cat): ?>
      <div class="col-6 col-md-4 col-lg-3">
        <a href="workers.php?category_id=<?= $cat['id'] ?>" class="cat-box">"""

new_cat_box = """    <?php
    $catImgMap = [
      'Plumber' => 'images/plumber.jpg',
      'Electrician' => 'images/electrician.jpg',
      'Mechanic' => 'images/mechanic.jpg',
      'AC Repair' => 'images/ac_repair.jpg',
      'Cleaner' => 'images/cleaner.jpg',
      'Painter' => 'images/painter.jpg',
      'Carpenter' => 'images/carpenter.jpg',
    ];
    $cIdx = 0;
    foreach ($categories as $cat): 
      $cIdx++;
      $img = $catImgMap[$cat['name']] ?? null;
    ?>
      <div class="col-6 col-md-4 col-lg-3" data-aos="fade-up" data-aos-delay="<?= min(350, $cIdx * 40) ?>">
        <a href="workers.php?category_id=<?= $cat['id'] ?>" class="cat-box hover-lift">"""

if old_cat_box in c:
    c = c.replace(old_cat_box, new_cat_box)

with open(r"C:\xampp\htdocs\e-sheba\categories.php", "w", encoding="utf-8") as f:
    f.write(c)

print("categories.php updated")
