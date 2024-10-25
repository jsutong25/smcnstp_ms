<?php
session_start();
include "./connect.php";
$message = "";

$user_id = $_SESSION['user_id'];
$section_id = isset($_GET['section_id']) ? $_GET['section_id'] : null;

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

$album = null;
$result_images = null; // Initialize the variable

if (isset($_GET['documentation_id'])) {
    $album_id = intval($_GET['documentation_id']); // Fetching album_id

    $sql = "SELECT * FROM documentation WHERE documentation_id = $album_id";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $album = mysqli_fetch_assoc($result);
    } else {
        echo "Album not found.";
        exit();
    }

    // Fetching images for the album
    $sql_images = "SELECT * FROM images WHERE documentation_id = $album_id";
    $result_images = mysqli_query($conn, $sql_images);

    // Fetch album name and section name from the database using a JOIN query
    $current_year = date('Y');

    $sql_album = "
        SELECT d.documentation_name, s.section_name 
        FROM documentation d
        JOIN section s ON d.section_id = s.section_id
        WHERE d.documentation_id = $album_id";

    $result_album = mysqli_query($conn, $sql_album);

    if ($result_album && mysqli_num_rows($result_album) > 0) {
        $row_album = mysqli_fetch_assoc($result_album);
        $album_name = $row_album['documentation_name'];
        $section_name = $row_album['section_name'];
        $upload_dir = "./uploads/" . $current_year . "/" . $album_name . "-" . $section_name; // Define the upload directory

        // Check if the upload directory exists, if not, create it
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true); // Create the directory
        }

        // Handle file upload
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
            $image = $_FILES['image'];
            $target_file = $upload_dir . '/' . basename($image['name']);

            // Move the uploaded file to the target directory
            if (move_uploaded_file($image['tmp_name'], $target_file)) {
                // Prepare SQL to insert into the database
                $uploaded_by = $_SESSION['user_id']; // Assuming you store user_id in session
                $sql_insert = "INSERT INTO images (documentation_id, image, uploaded_by) VALUES ($album_id, '$target_file', $uploaded_by)";
                mysqli_query($conn, $sql_insert);
                $_SESSION['message'] = "Image uploaded successfully.";
                header("Location: ./view_album.php?documentation_id=$album_id&section_id=$section_id");
                exit();
            } else {
                $_SESSION['message'] = "Failed to upload image.";
            }
        }
    }
} else {
    $_SESSION['message'] = "Invalid album.";
    header("Location: ./documentation.php?section_id=$section_id");
    exit();
}

if (isset($_POST['delete_album'])) {
    // Delete album and all related images
    $sql_delete_album = "DELETE FROM documentation WHERE documentation_id = $album_id";
    $sql_delete_images = "DELETE FROM images WHERE documentation_id = $album_id";

    if (mysqli_query($conn, $sql_delete_images) && mysqli_query($conn, $sql_delete_album)) {
        // Optionally, delete the album's directory and its files from the server
        array_map('unlink', glob("$upload_dir/*.*")); // Delete all files in the album's directory
        rmdir($upload_dir); // Remove the directory

        $_SESSION['message'] = "Album and all related images deleted successfully.";
        header("Location: ./documentation.php?section_id=$section_id");
        exit();
    } else {
        $_SESSION['message'] = "Failed to delete the album.";
    }
}

$user_type = $_SESSION['user_type'];
$_SESSION['last_activity'] = time();

?>

<!doctype html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./output.css" rel="stylesheet">
    <title><?php echo htmlspecialchars($album['documentation_name']); ?></title>
    <style>
        /* Custom styles for the lightbox */
        .lightbox {
            display: none;
            /* Hidden by default */
        }
    </style>
</head>

<body class="bg-bg font-primary text-white my-8 mx-8 h-[100vh] overflow-auto">
    <!-- <nav class="flex flex-row">
    <img src="" alt="">
    <h2 class="text-[20px]">SMC NSTP</h2>
  </nav> -->

    <div class="">
        <div class="flex flex-row items-center gap-2">
            <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
                <span class="sr-only">Open sidebar</span>
                <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
                </svg>
            </button>
            <a href="./faculty/faculty_home.php?section_id=<?php echo $section_id; ?>"><span class="text-lg">SMC NSTP</span></a>
        </div>

        <div class="mt-4 p-2 flex-grow sm:ml-64">
            <a href="./documentation.php?section_id=<?php echo $section_id; ?>"><svg class="transition ease-in-out hover:text-primary" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 42 42">
                    <path fill="currentColor" fill-rule="evenodd" d="M27.066 1L7 21.068l19.568 19.569l4.934-4.933l-14.637-14.636L32 5.933z" />
                </svg></a>
        </div>

        <div class="flex h-screen">
        <?php if ($_SESSION['user_type'] == 'nstp_coordinator' || $_SESSION['user_type'] == 'faculty'): ?>
                <?php include './sidebar_faculty-src.php'; ?>
            <?php else: ?>
                <?php include './sidebar_student-src.php'; ?>
            <?php endif; ?>

            <div class="flex-grow p-4 sm:ml-64">
                <div>
                    <div class="flex justify-between items-center">
                        <h2 class="text-[24px]"><?php echo htmlspecialchars($album['documentation_name']); ?></h2>

                        <!-- Display Edit and Delete buttons only for faculty or NSTP coordinator -->
                        <?php if ($user_type == 'faculty' || $user_type == 'nstp_coordinator'): ?>
                            <div>
                                <a href="./faculty/edit_documentation.php?documentation_id=<?php echo $album_id; ?>&section_id=<?php echo $section_id; ?>" class="bg-blue-500 text-white px-4 py-2 rounded">Edit</a>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this album?');">
                                    <input type="hidden" name="delete_album" value="1">
                                    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded">Delete</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mx-auto w-full flex mb-8 gap-2">
                        <form action="view_album.php?documentation_id=<?php echo $album_id; ?>&section_id=<?php echo $section_id; ?>" method="POST" enctype="multipart/form-data">
                            <input type="file" name="image" id="image">
                            <input type="submit" name="upload" value="Upload image">
                        </form>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <?php if ($result_images && mysqli_num_rows($result_images) > 0): ?>
                            <?php while ($image_row = mysqli_fetch_assoc($result_images)): ?>
                                <div class="relative cursor-pointer" onclick="openLightbox('<?php echo $image_row['image']; ?>', <?php echo $image_row['image_id']; ?>)">
                                    <img src="<?php echo $image_row['image']; ?>" alt="Uploaded Image" class="w-full h-64 object-cover rounded-lg shadow-md" />
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-gray-500">No images uploaded in this album.</p>
                        <?php endif; ?>
                    </div>

                    <div id="lightbox" class="fixed top-0 left-0 w-full h-full bg-black bg-opacity-50 flex justify-center items-center lightbox" onclick="closeLightbox()">
                        <span class="absolute left-4 text-white outline-2 outline-black text-[50px] cursor-pointer z-10" onclick="changeImage(-1, event)">&#10094;</span>
                        <img id="lightboxImage" src="" alt="Lightbox Image" class="max-w-[90%] max-h-[90%] rounded-lg z-1" />
                        <span class="absolute right-4 text-white outline-2 outline-black text-[50px] cursor-pointer z-10" onclick="changeImage(1, event)">&#10095;</span>
                    </div>

                </div>
            </div>

            <!-- <div>
                        <?php
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $album_name = urlencode($row['documentation_name']);
                        ?>
                                <div class="w-[100px]">
                                    <a href="../view_album.php?album=<?php echo $album_name; ?>">
                                        <div class="bg-white w-[100px] h-[100px]">
                                            <div class="flex h-full justify-center items-center mx-2 z-10">
                                                <span class="text-subtext text-[12px]">View album</span>
                                            </div>
                                            <a href="#"><img src="" alt=""></a>
                                        </div>
                                        <div class="">
                                            <p><?php echo $row['documentation_name']; ?></p>
                                        </div>
                                    </a>
                                </div>
                        <?php
                            }
                        } else {
                            echo "No documentation available.</p>";
                        }
                        ?>
                        
                    </div> -->

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

    <script>
        let currentImageIndex = -1;
        let images = [];

        function openLightbox(imageSrc) {
            const lightbox = document.getElementById('lightbox');
            const lightboxImage = document.getElementById('lightboxImage');

            lightbox.style.display = 'flex'; // Show the lightbox
            lightboxImage.src = imageSrc; // Set the image source

            // Store image information
            images = [];
            const imageWrappers = document.querySelectorAll('.grid div img');
            imageWrappers.forEach((img, index) => {
                images.push(img.src);
                if (img.src === imageSrc) {
                    currentImageIndex = index; // Set the current index
                }
            });

            // Add event listener for keyboard navigation
            document.addEventListener('keydown', handleKeyPress);
        }

        function closeLightbox(event) {
            const lightbox = document.getElementById('lightbox');
            lightbox.style.display = 'none'; // Hide the lightbox
            // Remove event listener
            document.removeEventListener('keydown', handleKeyPress);
            if (event) {
                event.stopPropagation(); // Prevent closing the lightbox when clicking on the close button
            }
        }

        function changeImage(direction, event) {
            event.stopPropagation(); // Prevent closing the lightbox when clicking the arrow buttons
            // Calculate the new index
            currentImageIndex += direction;
            if (currentImageIndex < 0) {
                currentImageIndex = images.length - 1; // Wrap to last image
            } else if (currentImageIndex >= images.length) {
                currentImageIndex = 0; // Wrap to first image
            }
            document.getElementById('lightboxImage').src = images[currentImageIndex]; // Change the image source
        }

        function handleKeyPress(event) {
            if (event.key === "ArrowLeft") {
                changeImage(-1, event); // Previous image
            } else if (event.key === "ArrowRight") {
                changeImage(1, event); // Next image
            } else if (event.key === "Escape") {
                closeLightbox(event); // Close lightbox
            }
        }
    </script>
</body>

</html>