with open(r"C:\xampp\htdocs\e-sheba\workers.php", "r", encoding="utf-8") as f:
    c = f.read()

if "https://unpkg.com/aos" not in c:
    c = c.replace(
        '<link rel="stylesheet" href="css/style.css">',
        '<link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">\n  <link rel="stylesheet" href="css/style.css">'
    )
    c = c.replace(
        '<script src="js/app.js"></script>',
        '<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>\n<script>AOS.init({duration:700,once:true});</script>\n<script src="js/app.js"></script>'
    )

old_loop = """        <?php foreach($workers as $w):
          $initial = strtoupper(mb_substr($w['name'],0,1));
          $stars   = $w['avg_rating'];
          $catIcon = explode(',',$w['icons']??'fa-tools')[0];
        ?>
        <div class="col-sm-6 col-xl-4">"""

new_loop = """        <?php
        $avatarFallback = ['images/worker_1.jpg','images/worker_2.jpg','images/worker_3.jpg','images/worker_4.jpg','images/worker_5.jpg'];
        $wCounter = 0;
        foreach($workers as $w):
          $initial = strtoupper(mb_substr($w['name'],0,1));
          $stars   = $w['avg_rating'];
          $catIcon = explode(',',$w['icons']??'fa-tools')[0];
          $wAvatar = !empty($w['avatar']) ? $w['avatar'] : $avatarFallback[$w['id'] % count($avatarFallback)];
          $wCounter++;
        ?>
        <div class="col-sm-6 col-xl-4" data-aos="fade-up" data-aos-delay="<?= min(350, $wCounter * 50) ?>">"""

if old_loop in c:
    c = c.replace(old_loop, new_loop)

old_img = """              <?php if($w['avatar']): ?>
                <img src="<?= htmlspecialchars($w['avatar']) ?>" alt="<?= htmlspecialchars($w['name']) ?>" loading="lazy">
              <?php else: ?>
                <div class="avatar-placeholder"><?= $initial ?></div>
              <?php endif; ?>"""

new_img = """              <img src="<?= htmlspecialchars($wAvatar) ?>" alt="<?= htmlspecialchars($w['name']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover;">"""

if old_img in c:
    c = c.replace(old_img, new_img)

with open(r"C:\xampp\htdocs\e-sheba\workers.php", "w", encoding="utf-8") as f:
    f.write(c)

print("workers.php successfully updated")
