<?php

session_start();
include "./connect.php";
$message = "";

$user_id = $_SESSION['user_id'];
$timeout_duration = 3600;

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    $_SESSION['message'] = "You are not logged in.";
    header("Location: ./index.php");
    exit();
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: ./index.php");
    exit();
}

$activities_result = null;
$documentation_result = null;

$sql_sections = "SELECT section_id, section_name FROM section WHERE faculty_id = ?";
$stmt = $conn->prepare($sql_sections);
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$sections_result = $stmt->get_result();

$section_id = isset($_GET['section_id']) ? $_GET['section_id'] : null;

if ($section_id) {
    // Prepare query for activities related to the selected section
    $activities_sql = "SELECT * FROM activities WHERE section_id = ? AND CONCAT(date, ' ', time) > NOW() ORDER BY date, time ASC";
    $stmt = $conn->prepare($activities_sql);
    if ($stmt) {
        $stmt->bind_param("i", $section_id);
        if ($stmt->execute()) {
            $activities_result = $stmt->get_result();
        } else {
            echo "Query execution failed: " . $stmt->error;
        }
    } else {
        echo "Query preparation failed: " . $conn->error;
    }

    // Prepare query for documentation related to the selected section
    $documentation_sql = "SELECT * FROM documentation WHERE section_id = ? ORDER BY created_at ASC";
    $stmt = $conn->prepare($documentation_sql);
    if ($stmt) {
        $stmt->bind_param("i", $section_id);
        if ($stmt->execute()) {
            $documentation_result = $stmt->get_result();
        } else {
            echo "Query execution failed: " . $stmt->error;
        }
    } else {
        echo "Query preparation failed: " . $conn->error;
    }
}

$sql = "SELECT * FROM documentation ORDER by created_at ASC";
$result = mysqli_query($conn, $sql);

$_SESSION['last_activity'] = time();

?>

<!doctype html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./output.css" rel="stylesheet">
    <title>Faculty</title>
</head>

<body class="bg-bg font-primary text-white my-8 mx-8 h-[100vh] overflow-y-hidden overflow-x-auto">

    <div class="container mx-auto">
        <div class="container mx-auto w-full">
            <div class="flex flex-row items-center gap-2 w-full md:hidden">
                <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
                    <span class="sr-only">Open sidebar</span>
                    <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
                    </svg>
                </button>

                <a href="./faculty/faculty_home.php?section_id=<?php echo $section_id; ?>"><span class="text-lg">SMC NSTP</span></a>
            </div>

            <div class="mt-4 p-2 flex-grow sm:ml-64">
            <?php if ($_SESSION['user_type'] == 'nstp_coordinator' || $_SESSION['user_type'] == 'faculty'): ?>
                <a href="./faculty/faculty_home.php?section_id=<?php echo $section_id; ?>"><svg class="transition ease-in-out hover:text-primary" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 42 42">
                        <path fill="currentColor" fill-rule="evenodd" d="M27.066 1L7 21.068l19.568 19.569l4.934-4.933l-14.637-14.636L32 5.933z" />
                    </svg></a>
            <?php else: ?>
                <a href="./student/student_home.php?section_id=<?php echo $section_id; ?>"><svg class="transition ease-in-out hover:text-primary" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 42 42">
                        <path fill="currentColor" fill-rule="evenodd" d="M27.066 1L7 21.068l19.568 19.569l4.934-4.933l-14.637-14.636L32 5.933z" />
                    </svg></a>
            <?php endif; ?>
            </div>

            <div class="flex h-screen w-full overflow-hidden">
            <?php if ($_SESSION['user_type'] == 'nstp_coordinator' || $_SESSION['user_type'] == 'faculty'): ?>
                <?php include './sidebar_faculty-src.php'; ?>
            <?php else: ?>
                <?php include './sidebar_student-src.php'; ?>
            <?php endif; ?>

                <div class="flex-grow p-4 sm:ml-64">

                    <!-- Documentation -->
                    <div class="h-full">
                        <div class="flex justify-between items-center">
                            <h2 class="text-[24px]">Documentation</h2>
                        </div>

                        <?php if ($_SESSION['user_type'] == 'nstp_coordinator' || $_SESSION['user_type'] == 'faculty'): ?>
                        <div class="mx-auto w-full flex mb-8 gap-2">
                            <a class="bg-primary py-3 text-center w-full rounded-full mt-8 hover:cursor-pointer hover:bg-red-700 flex items-center justify-center" href="./faculty/new_documentation.php?section_id=<?php echo $section_id; ?>">
                                <svg class="transition ease-linear duration-200 hover:text-primary mr-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                                    <g fill="currentColor" fill-rule="evenodd" clip-rule="evenodd">
                                        <path d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12Zm10-8a8 8 0 1 0 0 16a8 8 0 0 0 0-16Z" />
                                        <path d="M13 7a1 1 0 1 0-2 0v4H7a1 1 0 1 0 0 2h4v4a1 1 0 1 0 2 0v-4h4a1 1 0 1 0 0-2h-4V7Z" />
                                    </g>
                                </svg>
                                Create new album
                            </a>
                        </div>
                        <?php endif; ?>

                        <div class="grid grid-cols-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 mt-8">
                            <?php
                            // Fetch sections for the faculty
                            $sql_sections = "SELECT section_id, section_name FROM section WHERE faculty_id = ?";
                            $stmt = $conn->prepare($sql_sections);
                            $stmt->bind_param("i", $faculty_id);
                            $stmt->execute();
                            $sections_result = $stmt->get_result();

                            $section_id = isset($_GET['section_id']) ? $_GET['section_id'] : null;

                            // If a section is selected, fetch the documentation for that section
                            if ($section_id) {
                                // Prepare query for documentation related to the selected section
                                $documentation_sql = "SELECT d.documentation_id, d.documentation_name, s.section_name 
                              FROM documentation d
                              LEFT JOIN section s ON d.section_id = s.section_id 
                              WHERE d.section_id = ? 
                              ORDER BY d.created_at ASC"; // Fetch documentation for the selected section

                                $stmt = $conn->prepare($documentation_sql);
                                if ($stmt) {
                                    $stmt->bind_param("i", $section_id);
                                    if ($stmt->execute()) {
                                        $documentation_result = $stmt->get_result();
                                    } else {
                                        echo "Query execution failed: " . $stmt->error;
                                    }
                                } else {
                                    echo "Query preparation failed: " . $conn->error;
                                }
                            } else {
                                echo "<p class='italic text-center text-[16px] mt-4'>Select a section first.</p>";
                            }

                            // Check if documentation result is not null
                            if (isset($documentation_result) && $documentation_result !== null) {
                                // Check if there are rows returned
                                if (mysqli_num_rows($documentation_result) > 0) {
                                    while ($row = mysqli_fetch_assoc($documentation_result)) {
                                        $album_name = urlencode($row['documentation_name']);
                                        $album_id = $row['documentation_id'];

                                        // Get section name or set default
                                        $section_name = isset($row['section_name']) ? $row['section_name'] : 'Unknown Section';
                                        $current_year = date('Y');

                                        // Fetch the first image for the album
                                        $sql_thumbnail = "SELECT image FROM images WHERE documentation_id = ? LIMIT 1"; // Adjusted for prepared statement
                                        $thumbnail_stmt = $conn->prepare($sql_thumbnail);
                                        $thumbnail_stmt->bind_param("i", $album_id);
                                        $thumbnail_stmt->execute();
                                        $result_thumbnail = $thumbnail_stmt->get_result();
                                        $thumbnail = null;

                                        if ($result_thumbnail && mysqli_num_rows($result_thumbnail) > 0) {
                                            $row_thumbnail = mysqli_fetch_assoc($result_thumbnail);
                                            $thumbnail = $row_thumbnail['image']; // This is the full image path
                                        }

                                        // Define the directory structure for the album
                                        $upload_dir = "./uploads/" . $current_year . "/" . $row['documentation_name'] . "-" . $section_name;
                            ?>
                                        <div class="w-[100px] md:w-[120px] lg:w-[150px] xl:w-[180px]">
                                            <a href="./view_album.php?documentation_id=<?php echo $row['documentation_id']; ?>&section_id=<?php echo $section_id; ?>">
                                                <div class="bg-white w-[100px] h-[100px] md:w-[120px] md:h-[120px] lg:w-[150px] lg:h-[150px] xl:w-[180px] xl:h-[180px]">
                                                    <?php if ($thumbnail): ?>
                                                        <img src="<?php echo $thumbnail; ?>" alt="Album Thumbnail" class="w-full h-full object-cover">
                                                    <?php else: ?>
                                                        <div class="flex h-full justify-center items-center mx-2 z-10">
                                                            <span class="text-subtext text-[12px] lg:text-[20px]">View album</span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="">
                                                    <p class="lg:text-[20px]"><?php echo $row['documentation_name']; ?></p>
                                                </div>
                                            </a>
                                        </div>
                            <?php
                                    }
                                } else {
                                    echo "<p class='italic text-center text-[16px] mt-4'>No documentation available for this section.</p>";
                                }
                            }
                            ?>
                        </div>


                    </div>
                </div>
            </div>
        </div>

        <div id="messageModal" class="fixed top-0 inset-0 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-sm p-5 relative">
                <p class="mt-2 text-center text-gray-600"><?php echo $message; ?></p>
                <div class="mt-4 flex justify-center">
                    <button onclick="closeModal()" class="bg-primary text-white font-semibold py-2 px-4 rounded-full">Close</button>
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