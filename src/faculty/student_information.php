<?php

session_start();
include "../connect.php";
$message = "";
$user_type = $_SESSION['user_type'];
$section_id = isset($_GET['section_id']) ? $_GET['section_id'] : null;

if ($user_type == 'student') {
    header("Location: ../student/student_home.php?section_id=$section_id");
}

$timeout_duration = 3600;

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    $_SESSION['message'] = "You are not logged in.";
    header("Location: ../index.php");
    exit();
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit();
}

$activities_result = null;
$documentation_result = null;

$sql_sections = "SELECT section_id, section_name FROM section WHERE faculty_id = ?";
$stmt = $conn->prepare($sql_sections);
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$sections_result = $stmt->get_result();


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
    <link href="../output.css" rel="stylesheet">
    <title>Faculty</title>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css" />

    <style>
        /* Ensure the table has a light background */
        table.dataTable {
            background-color: white;
            /* Set table background to white */
            color: black;
            /* Set text color to black */
        }

        /* Adjust table header styling */
        table.dataTable thead th {
            background-color: #f1f1f1;
            /* Light gray header background */
            color: black;
            /* Black text for header */
            font-weight: bold;
            /* Bold header text */
        }

        /* Row hover effects */
        table.dataTable tbody tr:hover {
            background-color: #e0e0e0;
            /* Light gray on hover */
        }

        /* Table cell styling */
        table.dataTable tbody td {
            padding: 8px;
            /* Add some padding to cells */
            border-bottom: 1px solid #ddd;
            /* Light border for cells */
        }

        /* Table footer styling */
        table.dataTable tfoot th {
            background-color: #f1f1f1;
            /* Light gray footer background */
            color: black;
            /* Black text for footer */
        }

        /* Search input styling */
        div.dataTables_filter input {
            border: 2px solid #fff;
            /* Blue border */
            border-radius: 4px;
            /* Rounded corners */
            padding: 5px;
            /* Add some padding */
            margin-left: 0.5em;
            /* Space between label and input */
            outline: none;
            /* Remove outline */
            color: #fff;
            /* Text color */
        }

        /* Search input focus effect */
        div.dataTables_filter input:focus {
            border-color: #0056b3;
            /* Darker blue on focus */
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
            /* Subtle shadow effect */
        }

        /* Excel button styling */
        .dt-button {
            background-color: #fff;
            /* Green background */
            color: white;
            /* White text */
            border: none;
            /* No border */
            padding: 8px 12px;
            /* Padding */
            border-radius: 4px;
            /* Rounded corners */
            font-size: 14px;
            /* Font size */
            cursor: pointer;
            /* Pointer cursor on hover */
            margin-left: 10px;
            /* Space between buttons */
        }

        /* Excel button hover effect */
        .dt-button:hover {
            background-color: #218838;
            /* Darker green on hover */
        }

        /* Custom text for showing entries */
        div.dataTables_info {
            color: #333;
            /* Text color */
            font-weight: bold;
            /* Bold text */
        }
    </style>
</head>

<body class="bg-bg font-primary text-white my-8 mx-8 h-[100vh] overflow-y-hidden overflow-x-auto">


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

        <div class="mt-4 p-2 sm:ml-64">
            <a href="./faculty_home.php?section_id=<?php echo $section_id; ?>"><svg class="transition ease-in-out hover:text-primary" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 42 42">
                    <path fill="currentColor" fill-rule="evenodd" d="M27.066 1L7 21.068l19.568 19.569l4.934-4.933l-14.637-14.636L32 5.933z" />
                </svg></a>
        </div>

        <div class="flex h-screen w-full">
        <?php include '../sidebar_faculty.php'; ?>

            <div class="flex-grow p-4 sm:ml-64">

                <div class="h-full">
                    <div class="">
                        <h2 class="text-[24px]">Student Information</h2>
                    </div>

                    <div class="bg-white p-2 rounded-md">
                        <table id="student" class="display" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="uppercase">Award Year</th>
                                    <th class="uppercase">Program</th>
                                    <th class="uppercase">Region</th>
                                    <th class="uppercase">Serial Number</th>
                                    <th class="uppercase">Last Name</th>
                                    <th class="uppercase">First Name</th>
                                    <th class="uppercase">Middle Name</th>
                                    <th class="uppercase">Extension Name</th>
                                    <th class="uppercase">Birthdate</th>
                                    <th class="uppercase">Sex</th>
                                    <th class="uppercase">Barangay</th>
                                    <th class="uppercase">City</th>
                                    <th class="uppercase">Province</th>
                                    <th class="uppercase">HEI Name</th>
                                    <th class="uppercase">Institutional Code</th>
                                    <th class="uppercase">Types of HEIS</th>
                                    <th class="uppercase">Program Level Code</th>
                                    <th class="uppercase">Main Program Name</th>
                                    <th class="uppercase">Email Address</th>
                                    <th class="uppercase">Contact Number</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($section_id == 5) {
                                    $sql = "SELECT * FROM user WHERE user_type = 'student'";

                                } else {
                                    $sql = "SELECT * FROM user WHERE user_type = 'student' AND section = '$section_id'";
                                }
                                
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    $ayear = date("Y");
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>
                                                <td class='uppercase'>$ayear</td>
                                                <td class='uppercase'>{$row['program']}</td>
                                                <td class='uppercase'>10</td>
                                                <td class='uppercase'>{$row['serial_number']}</td>
                                                <td class='uppercase'>{$row['last_name']}</td>
                                                <td class='uppercase'>{$row['first_name']}</td>
                                                <td class='uppercase'>{$row['middle_name']}</td>
                                                <td class='uppercase'>{$row['extension_name']}</td>
                                                <td class='uppercase'>{$row['birthday']}</td>
                                                <td class='uppercase'>{$row['sex']}</td>
                                                <td class='uppercase'>{$row['barangay']}</td>
                                                <td class='uppercase'>{$row['city']}</td>
                                                <td class='uppercase'>{$row['province']}</td>
                                                <td class='uppercase'>St. Michael's College of Iligan, Inc.</td>
                                                <td class='uppercase'>12062</td>
                                                <td class='uppercase'>Private</td>
                                                <td class='uppercase'>340101</td>
                                                <td class='uppercase'>{$row['course']}</td>
                                                <td class='uppercase'>{$row['email']}</td>
                                                <td class='uppercase'>{$row['contact_number']}</td>
                                            </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='15'>No data found</td></tr>";
                                }

                                $conn->close();
                                ?>
                            </tbody>
                        </table>
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


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#student').DataTable({
                dom: 'Bfrtip',
                buttons: ['excel'],
                language: {
                    info: "Displaying _START_ to _END_ of _TOTAL_ entries", // Custom text for the entries display
                    emptyTable: "No data available", // Text when the table is empty
                    zeroRecords: "No matching records found", // Text when no records match
                }
            });
        });
    </script>

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