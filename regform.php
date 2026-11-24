<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catering Service Application Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .form-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            max-width: 600px;
            width: 100%;
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        .form-group textarea {
            resize: vertical;
        }

        .form-group input[type="submit"] {
            background-color: #2196F3;
            color: #fff;
            border: none;
            padding: 12px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            width: 100%;
        }

        .form-group input[type="submit"]:hover {
            background-color: #1976D2;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Catering Service Application</h2>
        <form action="submit.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="catering_name">Catering Service Name:</label>
                <input type="text" id="catering_name" name="catering_name" required>
            </div>

            <div class="form-group">
                <label for="owner_name">Owner Name:</label>
                <input type="text" id="owner_name" name="owner_name" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone" required>
            </div>

            <div class="form-group">
                <label for="address">Business Address:</label>
                <textarea id="address" name="address" rows="3" required></textarea>
            </div>

            <div class="form-group">
                <label for="cuisine">Cuisine Type:</label>
                <input type="text" id="cuisine_type" name="cuisine_type" placeholder="e.g., Italian, Filipino, Fast Food" required>
            </div>

            <div class="form-group">
                <label for="capacity">Maximum Capacity (Guests):</label>
                <input type="number" id="capacity" name="capacity" required>
            </div>

            <div class="form-group">
                <label for="bir_proof">Upload BIR Certificate (Proof of Legitimacy):</label>
                <input type="file" id="bir_proof" name="bir_proof" accept=".pdf,.jpg,.jpeg,.png" required>
            </div>

            <div class="form-group">
                <input type="submit" value="Submit Application">
            </div>
        </form>
    </div>
</body>
</html>
