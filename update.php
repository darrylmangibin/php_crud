<?php

$pdo = new PDO('mysql:host=localhost;port=3306;dbname=product_crud', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = $_GET['id'] ?? null;

if (!$id) {
  header('Location: index.php');
  exit;
}

$statement = $pdo->prepare("SELECT * FROM products WHERE id=:id");
$statement->bindValue(':id', $id);
$statement->execute();
$product = $statement->fetch(PDO::FETCH_ASSOC);

$errors = [];

$title = $product['title'];
$price = $product['price'];
$description = $product['description'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = $_POST['title'];
  $description = $_POST['description'];
  $price = $_POST['price'];

  if (!$title) {
    array_push($errors, 'Product title is required');
  }

  if (!$price) {
    array_push($errors, 'Product price is required');
  }

  if (!is_dir('images')) {
    mkdir('images');
  }

  if (empty($errors)) {
    $image = $_FILES['image'] ?? null;
    $imagePath = $product['image'];

    if ($image && $image['tmp_name']) {
      if ($product['image']) {
        unlink($product['image']);
      }

      $imagePath = 'images/' . rand(1, 1000000000000) . '/' . $image['name'];

      mkdir(dirname($imagePath));

      move_uploaded_file($image['tmp_name'], $imagePath);
    }

    $statement = $pdo->prepare(
      "UPDATE products
        SET 
          title = :title, 
          image = :image, 
          description = :description, 
          price = :price WHERE id = :id"
    );

    $statement->bindValue(':id', $id);
    $statement->bindValue(':title', $title);
    $statement->bindValue(':image', $imagePath);
    $statement->bindValue(':description', $description);
    $statement->bindValue(':price', $price);

    $statement->execute();

    header('Location: index.php');
  }
}
?>

<!doctype html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">


  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">

  <link rel="stylesheet" href="app.css">
  <title>Products Crud</title>
</head>

<body>
  <p>
    <a href="index.php" class="btn btn-secondary">Go Back to Products</a>
  </p>

  <h1>Edit Product</h1>

  <?php if (!empty($errors)) : ?>
    <div class="alert alert-danger">
      <?php foreach ($errors as $error) : ?>
        <div><?php echo $error ?></div>
      <?php endforeach ?>
    </div>
  <?php endif ?>

  <form action="" method="POST" enctype="multipart/form-data">
    <?php if ($product['image']) : ?>
      <img src="<?php echo $product['image'] ?>" class="update-image" alt="">
    <?php endif; ?>
    <div class="form-group">
      <label>Product Image</label>
      <br />
      <input type="file" name="image">
    </div>
    <div class="form-group">
      <label>Product Title</label>
      <input type="text" class="form-control" name="title" value="<?php echo $title ?>">
    </div>
    <div class="form-group">
      <label>Product Description</label>
      <textarea class="form-control" name="description" rows="4"><?php echo $product['description']; ?></textarea>
    </div>
    <div class="form-group">
      <label>Product price</label>
      <input type="number" step=".01" class="form-control" name="price" value="<?php echo $price ?>">
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
  </form>
</body>

</html>