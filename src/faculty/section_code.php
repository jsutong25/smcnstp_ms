<?php
session_start();
include '../connect.php';

$user_type = $_SESSION['user_type'];

$section_id = isset($_GET['section_id']) ? $_GET['section_id'] : null;

if ($user_type == 'student') {
    header("Location: ../student/student_home.php?section_id=$section_id");
}


if (!$section_id) {
    $_SESSION['message'] = 'Please select a section first to generate registration link.';
    header("Location: ./faculty_home.php?section_id=$section_id");
}

$sql = "SELECT section_name FROM section WHERE section_id = $section_id";
$result = mysqli_query($conn, $sql);
$section = mysqli_fetch_assoc($result);

if (!$section) {
    $_SESSION['message'] = 'Invalid section selected.';
    header("Location: ./faculty_home.php?section_id=$section_id");
    exit;
}

$section_name = $section['section_name'];

if ($section_name === "All") {
    $_SESSION['message'] = 'Please select an individual section.';
    header("Location: ./faculty_home.php?section_id=$section_id");
    exit;
}

function generateUniqueCode($conn) {
    do {
        // Using random_bytes and bin2hex to generate more randomness
        $code = bin2hex(random_bytes(5)); // Generates a 10-character hexadecimal code
        
        // Check if the generated code already exists
        $check_sql = "SELECT code FROM section WHERE code = '$code'";
        $result = mysqli_query($conn, $check_sql);
    } while (mysqli_num_rows($result) > 0); // Repeat until a unique code is generated

    return strtoupper($code); // Convert to uppercase for better readability
}

$current_code_sql = "SELECT code FROM section WHERE section_id = $section_id";
$current_code_result = mysqli_query($conn, $current_code_sql);
$current_code = mysqli_fetch_assoc($current_code_result)['code'] ?? null;

if (isset($_POST['generate_code'])) {
    // Ensure you retrieve the section_id from the posted form
    $section_id = mysqli_real_escape_string($conn, $_POST['section_id']);
    $new_code = generateUniqueCode($conn);
    
    // Update the section with the new code
    $update_sql = "UPDATE section SET code = '$new_code' WHERE section_id = '$section_id'";
    
    if (mysqli_query($conn, $update_sql)) {
        // Update the current code with the new code
        $current_code = $new_code; // Update the variable to reflect the new code
    } else {
        // Handle error if needed (optional)
        echo "<script>alert('Error updating the code. Please try again.');</script>";
    }
}

$sql = "SELECT section_name FROM section WHERE section_id = $section_id";
$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Registration Link</title>
    <link href="../output.css" rel="stylesheet">
</head>

<body class="bg-bg font-primary text-white my-8 mx-8 overflow-hidden">

    <div class="container mx-auto">
        <div class="flex flex-row items-center gap-2 md:hidden">
            <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
                <span class="sr-only">Open sidebar</span>
                <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
                </svg>
            </button>
            <a href="./faculty_home.php?section_id=<?php echo $section_id; ?>"><span class="text-lg">SMC NSTP</span></a>
        </div>

        <div class="mt-4 p-2 sm:ml-[230px]">
            <a href="./faculty_home.php?section_id=<?php echo $section_id; ?>"><svg class="transition ease-in-out hover:text-primary" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 42 42">
                    <path fill="currentColor" fill-rule="evenodd" d="M27.066 1L7 21.068l19.568 19.569l4.934-4.933l-14.637-14.636L32 5.933z" />
                </svg></a>
        </div>

        <div class="flex h-screen w-full overflow-hidden">
            <?php include '../sidebar_faculty.php'; ?>

            <div class="mx-auto mt-10">
                <form method="POST" class="mt-4">
                    <input type="hidden" name="section_id" value="<?php echo $section_id; ?>">
                    <button type="submit" name="generate_code" class="bg-primary text-white p-2 mt-4">Generate New Code</button>
                </form>

                <!-- Display the current code if it exists -->
                <p class="mt-4">
                    Current Registration Code: <strong><?php echo $current_code ? $current_code : 'No registration code generated yet for this section.'; ?></strong>
                </p>
            </div>

        </div>
    </div>

    <script>
        const button = document.querySelector('[data-drawer-toggle="logo-sidebar"]');
        const sidebar = document.getElementById('logo-sidebar');

        // Function to toggle the sidebar
        const toggleSidebar = () => {
            sidebar.classList.toggle('-translate-x-full');
        };

        // Event listener for the hamburger button
        button.addEventListener('click', toggleSidebar);

        // Event listener for clicks outside the sidebar
        document.addEventListener('click', (event) => {
            // Check if the click is outside the sidebar and the button
            if (!sidebar.contains(event.target) && !button.contains(event.target)) {
                sidebar.classList.add('-translate-x-full'); // Close the sidebar
            }
        });
    </script>

    <script>
        window.onload = function() {
            <?php if (!empty($message)): ?>
                document.getElementById('messageModal').classList.remove('hidden'); // Show modal
            <?php endif; ?>
        };

        function closeModal() {
            document.getElementById('messageModal').classList.add('hidden'); // Hide modal
        }
    </script>

</body>

</html>