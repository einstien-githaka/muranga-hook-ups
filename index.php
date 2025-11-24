<?php session_start(); include 'db.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MURANG'A HOOK'S UP</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
        body{background:linear-gradient(135deg,#0f001a,#2a0033);color:#fff;min-height:100vh;text-align:center;padding:20px;}
        .title{font-size:4.5rem;font-weight:900;background:linear-gradient(to right,#ff0066,#ff3399,#ff66cc);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
        .btn{display:inline-block;padding:18px 40px;margin:15px;font-size:1.3rem;border-radius:50px;text-decoration:none;font-weight:bold;transition:0.4s;}
        .btn.provider{background:#ff0066;color:white;}
        .btn.pay{background:#00d4aa;color:white;}
        .btn.whatsapp{background:#25D366;color:white;padding:16px 35px;border-radius:50px;font-size:1.5rem;}
        .btn:hover,.btn.whatsapp:hover{transform:scale(1.1);}
        .form{background:rgba(0,0,0,0.6);padding:40px;border-radius:20px;max-width:500px;margin:30px auto;backdrop-filter:blur(10px);}
        .form input,.form select{width:100%;padding:16px;margin:12px 0;border:none;border-radius:12px;background:rgba(255,255,255,0.1);color:white;}
        .form input::placeholder{color:#ff99cc;}
        .form button{background:#ff0066;color:white;padding:16px;border:none;border-radius:50px;width:100%;font-size:1.3rem;cursor:pointer;margin-top:20px;}
        .profile-card{background:rgba(255,255,255,0.1);padding:25px;border-radius:20px;margin:20px auto;max-width:420px;}
        .profile-card img{width:160px;height:160px;object-fit:cover;border-radius:50%;border:5px solid #ff3399;}
        .gallery img{width:130px;height:190px;object-fit:cover;margin:6px;border-radius:12px;border:3px solid #ff3399;}
        .hidden{display:none;}
        .overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.95);z-index:999;display:flex;align-items:center;justify-content:center;}
        @media(max-width:600px){.title{font-size:3rem;}}
    </style>
</head>
<body>

<!-- 18+ AGE VERIFICATION -->
<?php if(!isset($_SESSION['age_verified'])): ?>
<div class="overlay">
    <div style="background:rgba(255,0,102,0.2);padding:50px;border-radius:20px;max-width:400px;">
        <h1 style="font-size:3rem;color:#ff3399;">18+ ONLY</h1>
        <p style="font-size:1.4rem;margin:20px;">You must be 18+ to enter this site</p>
        <form method="POST">
            <button name="verify_age" style="background:#ff0066;padding:20px 50px;font-size:1.5rem;border:none;border-radius:50px;color:white;">I am 18+</button>
        </form>
    </div>
</div>
<?php 
if(isset($_POST['verify_age'])) { $_SESSION['age_verified'] = true; header("Location: ?"); exit; }
endif; ?>

<?php
$page = $_GET['p'] ?? 'home';

// Client Login
if(isset($_POST['client_login'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $pass = $_POST['password'];
    $res = $conn->query("SELECT * FROM clients WHERE email='$email'");
    if($row = $res->fetch_assoc()) {
        if(password_verify($pass, $row['password'])) {
            $_SESSION['client_id'] = $row['id'];
            header("Location: ?p=browse");
        } else alert("Wrong password!");
    } else alert("Email not found!");
}

// Client Register
if(isset($_POST['client_register'])) {
    $name = $conn->real_escape_string($_POST['cname']);
    $email = $conn->real_escape_string($_POST['cemail']);
    $pass = password_hash($_POST['cpass'], PASSWORD_DEFAULT);
    $conn->query("INSERT INTO clients (name,email,password) VALUES ('$name','$email','$pass') ON DUPLICATE KEY UPDATE name='$name'");
    alert("Registered! Now login");
}

// Provider Register
if(isset($_POST['register_provider'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $phone = "254".preg_replace('/\D/','',substr($_POST['phone'],-9));
    $location = $conn->real_escape_string($_POST['location']);
    $price = (int)$_POST['price'];
    $profile = "default.jpg";
    if($_FILES['profile']['name']) {
        $profile = time()."_".$_FILES['profile']['name'];
        move_uploaded_file($_FILES['profile']['tmp_name'],"uploads/".$profile);
    }
    $conn->query("INSERT INTO providers (name,phone,location,price,profile_pic) VALUES ('$name','$phone','$location',$price,'$profile')");
    $_SESSION['provider_id'] = $conn->insert_id;
    header("Location: ?p=dashboard");
}

// Gallery Upload
if(isset($_FILES['photos']) && $_SESSION['provider_id']) {
    foreach($_FILES['photos']['tmp_name'] as $k=>$tmp) {
        if($tmp) {
            $img = time().$k.".jpg";
            move_uploaded_file($tmp,"uploads/".$img);
            $id = $_SESSION['provider_id'];
            $conn->query("INSERT INTO gallery (provider_id,image) VALUES ($id,'$img')");
        }
    }
    header("Location: ?p=dashboard");
}

// Fake M-Pesa Payment
if(isset($_POST['pay_mpesa'])) {
    $lady_id = $_POST['lady_id'];
    $lady = $conn->query("SELECT * FROM providers WHERE id=$lady_id")->fetch_assoc();
    $_SESSION['paid_for_'.$lady_id] = true;
    echo "<script>alert('M-Pesa STK Push sent! Payment successful!'); setTimeout(()=>location.href='?p=browse',2000);</script>";
}
?>

<!-- HOME -->
<?php if($page=='home'): ?>
<div>
    <h1 class="title">MURANG'A HOOK'S UP</h1>
    <p style="font-size:1.6rem;margin:30px;">Murang'a Hottest Connection Spot</p>
    <a href="?p=provider" class="btn provider">I'm a Lady (Join)</a><br>
    <a href="?p=browse" class="btn client">Browse Queens</a><br>
    <a href="?p=login" class="btn" style="background:#9900cc;">Client Login</a>
</div>
<?php endif; ?>

<!-- CLIENT LOGIN -->
<?php if($page=='login'): ?>
<div>
    <h1 class="title">Client Login</h1>
    <form method="POST" class="form">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button name="client_login">Login</button>
    </form>
    <p><a href="?p=register" style="color:#ff66cc;">No account? Register</a></p>
    <a href="?" class="back">Back</a>
</div>
<?php endif; ?>

<!-- CLIENT REGISTER -->
<?php if($page=='register'): ?>
<div>
    <h1 class="title">Client Register</h1>
    <form method="POST" class="form">
        <input type="text" name="cname" placeholder="Your Name" required>
        <input type="email" name="cemail" placeholder="Email" required>
        <input type="password" name="cpass" placeholder="Password" required>
        <button name="client_register">Register</button>
    </form>
    <a href="?" class="back">Back</a>
</div>
<?php endif; ?>

<!-- PROVIDER REGISTER -->
<?php if($page=='provider'): ?>
<div>
    <h1 class="title">Join as a Lady</h1>
    <form method="POST" enctype="multipart/form-data" class="form">
        <input type="text" name="name" placeholder="Your Sexy Name" required>
        <input type="tel" name="phone" placeholder="WhatsApp 07xxxxxxxxx" required>
        <select name="location" required>
            <option>Murang'a Town</option><option>Kangema</option><option>Kenol</option><option>Maragua</option><option>Other</option>
        </select>
        <input type="number" name="price" placeholder="Price per meet (e.g. 1500)" required>
        <input type="file" name="profile" accept="image/*">
        <button name="register_provider">Join Now</button>
    </form>
    <a href="?" class="back">Back</a>
</div>
<?php endif; ?>

<!-- BROWSE LADIES -->
<?php if($page=='browse'): ?>
<div>
    <h1 class="title">Available Queens</h1>
    <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:20px;">
    <?php
    $res = $conn->query("SELECT * FROM providers ORDER BY id DESC");
    while($lady = $res->fetch_assoc()) {
        $paid = isset($_SESSION['paid_for_'.$lady['id']]);
        $wa = $paid ? "https://wa.me/{$lady['phone']}" : "#";
        echo "<div class='profile-card'>
            <img src='uploads/{$lady['profile_pic']}' />
            <h2>{$lady['name']}</h2>
            <p>Location: {$lady['location']}</p>
            <p style='color:#00d4aa;font-size:1.4rem;'>Ksh {$lady['price']}</p>";
        
        if($paid) {
            echo "<a href='$wa' target='_blank' class='btn whatsapp'>Chat on WhatsApp</a>";
        } else {
            echo "<form method='POST'>
                <input type='hidden' name='lady_id' value='{$lady['id']}'>
                <button name='pay_mpesa' class='btn pay'>Pay Ksh {$lady['price']} (M-Pesa)</button>
            </form>";
        }
        
        echo "<div class='gallery'>";
        $g = $conn->query("SELECT image FROM gallery WHERE provider_id={$lady['id']}");
        while($img = $g->fetch_assoc()) echo "<img src='uploads/{$img['image']}' />";
        echo "</div></div>";
    }
    ?>
    </div>
    <a href="?" class="back">Back</a>
</div>
<?php endif; ?>

<!-- PROVIDER DASHBOARD -->
<?php if($page=='dashboard' && isset($_SESSION['provider_id'])): 
$id = $_SESSION['provider_id'];
$lady = $conn->query("SELECT * FROM providers WHERE id=$id")->fetch_assoc(); ?>
<div>
    <h1 class="title">Welcome Queen <?= $lady['name'] ?></h1>
    <div class="profile-card">
        <img src="uploads/<?= $lady['profile_pic'] ?>" />
        <h2><?= $lady['name'] ?></h2>
        <p>Location: <?= $lady['location'] ?></p>
        <p>Price: Ksh <?= $lady['price'] ?></p>
        <a href="https://wa.me/<?= $lady['phone'] ?>" class="btn whatsapp">My WhatsApp</a>
    </div>
    <form method="POST" enctype="multipart/form-data" class="form">
        <input type="file" name="photos[]" multiple accept="image/*">
        <button>Upload Gallery</button>
    </form>
    <div class="gallery">
        <?php $g = $conn->query("SELECT image FROM gallery WHERE provider_id=$id");
        while($img = $g->fetch_assoc()) echo "<img src='uploads/{$img['image']}' />"; ?>
    </div>
    <a href="?logout=1" class="back">Logout</a>
</div>
<?php if(isset($_GET['logout'])) { session_destroy(); header("Location: ?"); } ?>
<?php endif; ?>

<script>function alert(m){alert(m);}</script>
</body>
</html>