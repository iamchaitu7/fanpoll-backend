<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fan poll World || Account Deletion Request</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f9;
        }

        .form-container {
            background: #ffffff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .form-container h1 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: #333;
        }

        .form-container p {
            font-size: 1rem;
            margin-bottom: 20px;
            color: #555;
        }

        .form-container input[type="email"] {
            width: calc(100% - 20px);
            padding: 12px;
            margin: 15px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
            display: block;
            text-align: center;
        }

        .form-container button {
            background-color: #007bff;
            color: #fff;
            padding: 12px 18px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out;
            width: 100%;
        }

        .form-container button:hover {
            background-color: #0056b3;
        }

        .toast {
            visibility: hidden;
            max-width: 320px;
            background-color: #28a745;
            color: white;
            text-align: center;
            border-radius: 6px;
            padding: 12px;
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            font-size: 1rem;
            z-index: 1000;
        }

        .toast.show {
            visibility: visible;
            animation: fadeInOut 3.5s;
        }

        @keyframes fadeInOut {

            0%,
            100% {
                opacity: 0;
            }

            20%,
            80% {
                opacity: 1;
            }
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h1>Fan poll World</h1>
        <p>Submit a request to delete your account.</p>
        <form id="deleteAccountForm">
            <input type="email" id="email" placeholder="Enter your email" required>
            <button type="submit">Submit Request</button>
        </form>
    </div>

    <div id="toast" class="toast">Your delete request has been submitted successfully.</div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script>
        document.getElementById('deleteAccountForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const emailInput = document.getElementById('email');
            if (emailInput.value.trim() === '') {
                alert('Please enter a valid email address.');
                return;
            }

            $.ajax({
                url: '<?php echo base_url('page/submit_delete_account'); ?>',
                type: 'POST',
                data: {
                    email: emailInput.value
                },
                success: function(response_raw) {
                    let response = JSON.parse(response_raw);
                    if (response.status === 200) {
                        const toast = document.getElementById('toast');
                        toast.classList.add('show');

                        setTimeout(() => {
                            toast.classList.remove('show');
                        }, 3500);

                        emailInput.value = '';
                    } else {
                        alert('Failed to send delete request. Please try again.');
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        });
    </script>
</body>

</html>