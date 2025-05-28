<?php 
include 'includes/db.php';
session_start();

$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

$user_id = $_SESSION['user_id'] ?? 0;
$user_role = $_SESSION['user']['role'] ?? '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user_id) {
    $rating = intval($_POST['rating']);
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    // Insert review
    $sql = "INSERT INTO Reviews (product_id, user_id, rating, comment) 
            VALUES ('$product_id', '$user_id', '$rating', '$comment')";
    mysqli_query($conn, $sql);

    // If shop owner rates 4 or 5 stars, add product to favorites
    if ($user_role === 'Shop Owner' && ($rating >= 4)) {
        // Fetch product info with brand name from Users table
        $prod_sql = "SELECT P.name AS product_name, P.image_url AS product_image, P.price, P.description, U.name AS brand_name
                     FROM Products P
                     LEFT JOIN Users U ON P.brand_id = U.user_id
                     WHERE P.product_id = $product_id
                     LIMIT 1";
        $result = mysqli_query($conn, $prod_sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $prod = mysqli_fetch_assoc($result);

            // Insert into favorites, avoid duplicates
            $fav_sql = "INSERT IGNORE INTO favourites 
                (user_id, product_id, product_name, brand_name, product_image, price, description) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $fav_sql);
            mysqli_stmt_bind_param($stmt, "iisssds", 
                $user_id,
                $product_id,
                $prod['product_name'],
                $prod['brand_name'],
                $prod['product_image'],
                $prod['price'],
                $prod['description']
            );
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    header("Location: reviews.php?product_id=$product_id");
    exit();
}

// Get product name
$product_query = mysqli_query($conn, "SELECT name FROM Products WHERE product_id = $product_id");
$product = mysqli_fetch_assoc($product_query);

// Get reviews
$reviews = mysqli_query($conn, "
    SELECT Reviews.*, Users.name 
    FROM Reviews 
    JOIN Users ON Reviews.user_id = Users.user_id 
    WHERE product_id = $product_id 
    ORDER BY review_date DESC
");
?>


<!DOCTYPE html>
<html>
<head>
    <title>Product Notes</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
         
        /* Container */
        
        .container {
            max-width: 800px;
            margin: 100px auto 60px; /* top margin for navbar spacing */
            padding: 20px;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            border-radius: 16px;
            box-shadow: 0 16px 32px rgba(0,0,0,0.12);
            color: #1e293b;
            font-family: 'Poppins', sans-serif;
        }

        .page-title {
            text-align: center;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 30px;
            color:rgb(195, 199, 207);
        }

        form.form {
            display: flex;
            flex-direction: column;
            gap: 18px;
            margin-bottom: 40px;
        }

        form.form label {
            font-weight: 600;
            font-size: 16px;
            color: rgb(195, 199, 207);
        }

        form.form select, form.form textarea {
            padding: 12px 14px;
            font-size: 15px;
            border-radius: 12px;
            border: 1.8px solid #cbd5e1;
            transition: all 0.3s ease;
            resize: vertical;
            font-family: 'Poppins', sans-serif;
        }

        form.form select:focus, form.form textarea:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 8px rgba(59,130,246,0.3);
            outline: none;
            background-color: #fff;
        }

        form.form button.btn {
            background: linear-gradient(135deg, #4f46e5, #3b82f6, #06b6d4);
            color: #fff;
            font-weight: 800;
            font-size: 18px;
            padding: 16px 0;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            transition: 
               background-position 0.6s ease,
               box-shadow 0.3s ease,
               transform 0.2s ease;
            max-width: 220px;
            align-self: center;
            background-size: 300% 300%;
            background-position: 0% 50%;
            box-shadow: 0 6px 15px rgba(59, 130, 246, 0.6);
}

        form.form button.btn:hover {
            background-position: 100% 50%;
             box-shadow: 0 10px 25px rgba(14, 165, 233, 0.9);
            transform: translateY(-4px) scale(1.05);
}

        form.form button.btn:active {
            transform: translateY(-2px) scale(1);
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.7);
}

/* Also a little spacing tweak on form inputs for balance */
       form.form select, form.form textarea {
            padding: 14px 18px;
            font-size: 16px;
            border-radius: 20px;
            border: 1.8px solid #cbd5e1;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
}


        hr {
            border: none;
            border-top: 1.5px solid #e2e8f0;
            margin: 40px 0;
        }

        h3 {
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 25px;
            font-size: 22px;
            text-align: center;
        }

        .review-box {
            background-color: #f8fafc;
            border: 1.5px solid #cbd5e1;
            padding: 20px 24px;
            margin-bottom: 20px;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.05);
            transition: box-shadow 0.3s ease;
        }

        .review-box:hover {
            box-shadow: 0 12px 24px rgba(0,0,0,0.1);
        }

        .review-box strong {
            font-size: 17px;
            color: #1e293b;
        }

        .stars {
            color: #fbbf24; /* gold */
            margin-left: 8px;
            font-size: 18px;
            vertical-align: middle;
        }

        .review-box p {
            font-size: 15px;
            margin: 12px 0 10px;
            color: #475569;
            line-height: 1.5;
            white-space: pre-wrap;
        }

        .review-box small {
            font-size: 13px;
            color: #94a3b8;
            display: block;
            text-align: right;
        }

        p a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
        }

        p a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .container {
                margin: 80px 10px 40px;
                padding: 15px;
            }

            form.form button.btn {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container">
    <?php if ($product): ?>
        <h2 class="page-title">Notes "<?php echo htmlspecialchars($product['name']); ?>"</h2>
    <?php else: ?>
        <h2 class="page-title">Product not found.</h2>
    <?php endif; ?>

    <?php if ($user_id && $product): ?>
    <form method="POST" class="form">
        <label for="rating">Rating:</label>
        <select name="rating" id="rating" required>
            <option value="">-- Select --</option>
            <?php for ($i = 5; $i >= 1; $i--): ?>
                <option value="<?php echo $i; ?>"><?php echo $i; ?> Star<?php echo $i > 1 ? 's' : ''; ?></option>
            <?php endfor; ?>
        </select>

        <label for="comment">Comment:</label>
        <textarea name="comment" id="comment" rows="4" required></textarea>

        <button type="submit" class="btn">Submit Review</button>
    </form>
    <?php elseif (!$product): ?>
        <p>Cannot submit reviews for a product that doesn't exist.</p>
    <?php else: ?>
        <p><a href="login.php">Log in</a> to submit a review.</p>
    <?php endif; ?>

    <?php if ($product): ?>
        <hr>
        <h3>What others say:</h3>
        <?php if (mysqli_num_rows($reviews) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($reviews)): ?>
                <div class="review-box">
                    <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                    <span class="stars"><?php echo str_repeat('⭐', $row['rating']); ?></span>
                    <p><?php echo nl2br(htmlspecialchars($row['comment'])); ?></p>
                    <small><?php echo date("F j, Y, g:i a", strtotime($row['review_date'])); ?></small>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No reviews yet. Be the first! 💬</p>
        <?php endif; ?>
    <?php endif; ?>
</div>

</body>
</html>
