<div class="flex-grow p-4 sm:ml-64 md:ml-52 lg:ml-64 overflow-hidden max-w-full">
    <h1 class="text-[22px] mb-8">NSTP Coordinator</h1>

    <div class="flex space-x-4 mb-8 overflow-hidden">
        <!-- Normal Section Links (Fetched from the database) -->
        <?php if ($sections_result && mysqli_num_rows($sections_result) > 0): ?>
            <?php
            // Get the selected section_id from the query parameter
            $selected_section_id = isset($_GET['section_id']) ? $_GET['section_id'] : null;
            ?>
            <?php while ($section = mysqli_fetch_assoc($sections_result)): ?>
                <a href="?section_id=<?php echo $section['section_id']; ?>"
                    class="<?php echo ($selected_section_id == $section['section_id'])
                                ? 'border-2 border-red-900 text-white py-2 px-4 rounded'  // Styling for selected section
                                : 'bg-primary text-white py-2 px-4 rounded hover:bg-red-700'; // Default styling for other sections 
                            ?>">
                    <?php echo $section['section_name']; ?>
                </a>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>


    <h2 class="text-[18px] text-primary mb-7 hover:text-red-900"><a href="./student_information.php?section_id=<?php echo $section_id; ?>">Student Information</a></h2>
    <h2 class="text-[18px] text-primary mb-7 hover:text-red-900"><a href="./faculty_information.php?section_id=<?php echo $section_id; ?>">Faculty Information</a></h2>

    <!-- Upcoming activities -->
    <div class="w-full">
        <div class="flex justify-between items-center">
            <h2 class="text-[18px] mb-4 hover:text-gray-400"><a href="../activities.php?section_id=<?php echo $section_id; ?>">Upcoming Activities</a></h2>
            <a href="./new_activity.php?section_id=<?php echo $section_id; ?>">
                <svg class="transition-colors hover:text-primary ease-in-out" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 16 16">
                    <path fill="currentColor" d="M8 15c-3.86 0-7-3.14-7-7s3.14-7 7-7s7 3.14 7 7s-3.14 7-7 7ZM8 2C4.69 2 2 4.69 2 8s2.69 6 6 6s6-2.69 6-6s-2.69-6-6-6Z" />
                    <path fill="currentColor" d="M8 11.5c-.28 0-.5-.22-.5-.5V5c0-.28.22-.5.5-.5s.5.22.5.5v6c0 .28-.22.5-.5.5Z" />
                    <path fill="currentColor" d="M11 8.5H5c-.28 0-.5-.22-.5-.5s.22-.5.5-.5h6c.28 0 .5.22.5.5s-.22.5-.5.5Z" />
                </svg>
            </a>
        </div>

        <div>
            <?php
            if ($activities_result !== null) {
                if (mysqli_num_rows($activities_result) > 0) {
                    $counter = 0;
                    while ($row = mysqli_fetch_assoc($activities_result)) {
                        if ($counter >= 3) {
                            break;
                        }
                        $formatted_date = date("F j, Y", strtotime($row['date']));
                        $formatted_time = date("h:i A", strtotime($row['time']));
                        $activity_name = $row['activity_name'];
                        $description = $row['description'];

                        // Limit characters for mobile view
                        $short_title = substr($activity_name, 0, 25) . (strlen($activity_name) > 25 ? '...' : '');
                        $short_description = substr($description, 0, 10) . (strlen($description) > 20 ? '...' : '');
            ?>
                        <div class="flex gap-1 mb-4">
                            <!-- Date and time section -->
                            <div class="w-[100px] flex flex-col text-subtext items-start">
                                <p class="text-[16px]"><?php echo $formatted_date; ?></p>
                                <p class="text-[16px]"><?php echo $formatted_time; ?></p>
                            </div>

                            <!-- Vertical separator -->
                            <div class="w-1.5 h-auto bg-gray-500"></div>

                            <!-- Activity name and description -->
                            <a class="transition ease-linear hover:bg-gray-400 hover:bg-opacity-15 w-full sm:w-12 md:w-fit lg:w-full mx-8 ml-5" href="../view_activity.php?activity_id=<?php echo $row['activity_id']; ?>&section_id=<?php echo $section_id; ?>">
                                <div class="flex-1">
                                    <!-- Title: show full on larger screens and truncated on mobile -->
                                    <h5 class="block md:hidden"><?php echo $short_title; ?></h5>
                                    <h5 class="hidden md:block text-[16px]"><?php echo $activity_name; ?></h5>

                                    <!-- Description: truncated on mobile, full text for large screens -->
                                    <p class="block md:hidden text-subtext"><?php echo $short_description; ?></p>
                                    <p class="hidden md:block text-subtext">
                                        <?php echo (strlen($description) > 40) ? substr($description, 0, 40) . '...' : $description; ?>
                                    </p>
                                </div>
                            </a>
                        </div>
            <?php
                        $counter++;
                    }
                } else {
                    echo "<p class='italic text-center text-[16px] mt-4'>No upcoming activities for this section.</p>";
                }
            } else {
                echo "<p class='italic text-center text-[16px]'>Select a section above to see activities.</p>";
            }
            ?>
        </div>
        <!-- <a class="transition ease-in-out hover:text-primary hover:underline text-sm" href="">More activities...</a> -->
    </div>

    <!-- Documentation -->
    <div class="pt-5 w-full">
        <div class="flex justify-between items-center mt-8 mb-8">
            <h2 class="text-[18px] hover:text-gray-400"><a href="../documentation.php?section_id=<?php echo $section_id; ?>">Documentation</a></h2>
            <a href="./new_documentation.php?section_id=<?php echo $section_id; ?>">
                <svg class="transition-colors hover:text-primary ease-in-out" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 16 16">
                    <path fill="currentColor" d="M8 15c-3.86 0-7-3.14-7-7s3.14-7 7-7s7 3.14 7 7s-3.14 7-7 7ZM8 2C4.69 2 2 4.69 2 8s2.69 6 6 6s6-2.69 6-6s-2.69-6-6-6Z" />
                    <path fill="currentColor" d="M8 11.5c-.28 0-.5-.22-.5-.5V5c0-.28.22-.5.5-.5s.5.22.5.5v6c0 .28-.22.5-.5.5Z" />
                    <path fill="currentColor" d="M11 8.5H5c-.28 0-.5-.22-.5-.5s.22-.5.5-.5h6c.28 0 .5.22.5.5s-.22.5-.5.5Z" />
                </svg>
            </a>
        </div>

        <div class="grid grid-cols-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
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
                            $thumbnail = '.' . $row_thumbnail['image']; // This is the full image path
                        }

                        // Define the directory structure for the album
                        $upload_dir = "./uploads/" . $current_year . "/" . $row['documentation_name'] . "-" . $section_name;
            ?>
                        <div class="w-[100px] md:w-[120px] lg:w-[150px] xl:w-[180px]">
                            <a href="../view_album.php?documentation_id=<?php echo $row['documentation_id']; ?>&section_id=<?php echo $section_id; ?>">
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