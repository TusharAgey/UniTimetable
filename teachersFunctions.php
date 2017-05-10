<?php
//include js
function utt_teacher_scripts(){
    //include teacher scripts
    wp_enqueue_script( 'teacherScripts',  plugins_url('js/teacherScripts.js', __FILE__) );
    //localize teacher scripts
    wp_localize_script( 'teacherScripts', 'teacherStrings', array(
        'deleteForbidden' => __( 'Delete is forbidden while completing the form!', 'UniTimetable' ),
        'deleteRecord' => __( 'Are you sure that you want to delete this record?', 'UniTimetable' ),
        'teacherDeleted' => __( 'Teacher deleted successfully!', 'UniTimetable' ),
        'teacherNotDeleted' => __( 'Failed to delete Teacher. Check if Teacher is connected with a Lecture.', 'UniTimetable' ),
        'editForbidden' => __( 'Edit is forbidden while completing the form!', 'UniTimetable' ),
        'editTeacher' => __( 'Edit Teacher', 'UniTimetable' ),
        'cancel' => __( 'Cancel', 'UniTimetable' ),
        'surnameVal' => __( 'Surname field is required. Please avoid using special characters.', 'UniTimetable' ),
        'nameVal' => __( 'Please avoid using special characters at Name field.', 'UniTimetable' ),
        'minmaxWork' => __( 'Please provide valid working hours' ),
        'insertTeacher' => __( 'Insert Teacher', 'UniTimetable' ),
        'reset' => __( 'Reset', 'UniTimetable' ),
        'failAdd' => __( 'Failed to add Teacher. Check if the Teacher already exists.', 'UniTimetable' ),
        'successAdd' => __( 'Teacher successfully added!', 'UniTimetable' ),
        'failEdit' => __( 'Failed to edit Teacher. Check if the Teacher already exists.', 'UniTimetable' ),
        'successEdit' => __( 'Teacher successfully edited!', 'UniTimetable' ),
    ));
}

//teachers page
function utt_create_teachers_page(){
    //teachers form
?>
<div class="wrap" >
    <h2 id="teacherTitle"><?php _e('Insert Teacher','UniTimetable'); ?></h2>
    <form action="" name="teacherForm" method="post">
        <input type="hidden" name="teacherid" id="teacherid" value=0 />
        <?php _e("Surname:","UniTimetable"); ?><br/>
        <input type="text" name="lastname" id="lastname" class="dirty" required placeholder="<?php _e("Required","UniTimetable"); ?>"/>
        <br/>
        <?php _e("Name:","UniTimetable"); ?><br/>
        <input type="text" name="firstname" id="firstname" class="dirty" required placeholder="<?php _e("Required","UniTimetable"); ?>"/>
        <br/>
        <?php _e("Minimum workload:","UniTimetable"); ?><br/>
        <input type="text" name="minwork" id="minwork" class="dirty" required placeholder="<?php _e("Required","UniTimetable"); ?>"/>
        <br/>
        <?php _e("Maximum workload:","UniTimetable"); ?><br/>
        <input type="text" name="maxwork" id="maxwork" class="dirty" required placeholder="<?php _e("Required","UniTimetable"); ?>"/>
        <br/>
       
        <?php _e("Subject:","UniTimetable"); ?><br/>
        <select style="width:195px;" name="subject" id="subject" class="dirty">
            <?php
            //fill select with subjects
            global $wpdb;
            $subjectTable=$wpdb->prefix."utt_subjects";
            $subjects = $wpdb->get_results( "SELECT * FROM $subjectTable");
            echo "<option value='0'>".__("- select -","UniTimetable")."</option>";
            foreach($subjects as $sub){
                $subj = $sub->title;
                echo "<option value='$sub->subjectID'>$subj</option>";
            }
            ?>
        </select>
        <br/>
        <?php _e("Group:","UniTimetable"); ?><br/>
        <select style="width:195px;" name="group" id="group" class="dirty">
            <?php
            //fill select with groups
            global $wpdb;
            $groupsTable=$wpdb->prefix."utt_groups";
            $groups = $wpdb->get_results( "SELECT * FROM $groupsTable");
            echo "<option value='0'>".__("- select -","UniTimetable")."</option>";
            foreach($groups as $grp){
                echo "<option value='$grp->groupID'>$grp->groupName</option>";
            }
            ?>
        </select>

        <div id="secondaryButtonContainer">
        <input type="submit" value="<?php _e("Submit","UniTimetable"); ?>" id="insert-updateTeacher" class="button-primary"/>
        <a href='#' class='button-secondary' id="clearTeacherForm"><?php _e("Reset","UniTimetable"); ?></a>
        </div>
    </form>
    <!-- place to view messages -->
    <div id="messages"></div>
    <!-- place to view table with registered teachers -->
    <div id="teachersResults">
        <?php utt_view_teachers(); ?>
    </div>
</div>

<?php
}

add_action('wp_ajax_utt_view_teachers', 'utt_view_teachers');
function utt_view_teachers(){
    global $wpdb;
    $teachersTable=$wpdb->prefix."utt_teachers";
    $subjectsTable = $wpdb->prefix."utt_subjects";
    $groupsTable = $wpdb->prefix."utt_groups";
    //show registered teachers
    $teachers = $wpdb->get_results("SELECT * FROM $teachersTable ORDER BY surname");
    ?>
        <!-- table with registered teachers -->
        <table class="widefat bold-th">
            <thead>
                <tr>
                    <th>ID</th>
                    <th><?php _e("Surname","UniTimetable"); ?></th>
                    <th><?php _e("Name","UniTimetable"); ?></th>
                    <th><?php _e("Min workload","UniTimetable"); ?></th>
                    <th><?php _e("Max workload","UniTimetable"); ?></th>
                    <th><?php _e("Assigned workload","UniTimetable"); ?></th>
                    <th><?php _e("Subject", "UniTimetable"); ?></th>
                    <th><?php _e("Group", "UniTimetable"); ?></th>
                    <th><?php _e("Actions","UniTimetable"); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>ID</th>
                    <th><?php _e("Surname","UniTimetable"); ?></th>
                    <th><?php _e("Name","UniTimetable"); ?></th>
                    <th><?php _e("Min workload","UniTimetable"); ?></th>
                    <th><?php _e("Max workload","UniTimetable"); ?></th>
                    <th><?php _e("Assigned workload","UniTimetable"); ?></th>
                    <th><?php _e("Subject", "UniTimetable"); ?></th>
                    <th><?php _e("Group", "UniTimetable"); ?></th>
                    <th><?php _e("Actions","UniTimetable"); ?></th>
                </tr>
            </tfoot>
            <tbody>
        <?php
        //show grey and white records in order to be more recognizable
        $bgcolor = 1;
        foreach($teachers as $teacher){
            if($bgcolor == 1){
                $addClass = "class='grey'";
                $bgcolor = 2;
            }else{
                $addClass = "class='white'";
                $bgcolor = 1;
            }
            //a record
            $safeSql = $wpdb->prepare("SELECT * FROM $subjectsTable WHERE subjectID=%d",$teacher->subjectID);
            $subject = $wpdb->get_row($safeSql);
            $safeSql = $wpdb->prepare("SELECT * FROM $groupsTable WHERE groupID=%d",$teacher->groupID);
            $group = $wpdb->get_row($safeSql);

            echo "<tr id='$teacher->teacherID' $addClass><td>$teacher->teacherID</td><td>$teacher->surname</td><td>$teacher->name</td><td>$teacher->minWorkLoad</td><td>$teacher->maxWorkLoad</td><td>$teacher->assignedWorkLoad</td><td>$subject->title</td> <td>$group->groupName</td> <td><a href='#' onclick='deleteTeacher($teacher->teacherID);' class='deleteTeacher'><img id='edit-delete-icon' src='".plugins_url('icons/delete_icon.png', __FILE__)."'/> ".__("Delete","UniTimetable")."</a>&nbsp; <a href='#' onclick=\"editTeacher($teacher->teacherID, '$teacher->surname', '$teacher->name', $teacher->minWorkLoad, $teacher->maxWorkLoad);\" class='editTeacher'><img id='edit-delete-icon' src='".plugins_url('icons/edit_icon.png', __FILE__)."'/> ".__("Edit","UniTimetable")."</a></td></tr>";
        }
        
        ?>
            </tbody>
        </table>
        <?php
        die();
}

//ajax response delete teacher
add_action('wp_ajax_utt_delete_teacher', 'utt_delete_teacher');
function utt_delete_teacher(){
    global $wpdb;
    $teachersTable=$wpdb->prefix."utt_teachers";
    $safeSql = $wpdb->prepare("DELETE FROM $teachersTable WHERE teacherID= %d ", $_GET['teacher_id']);
    $success = $wpdb->query($safeSql);
    //if success is 1, delete succeeded
    echo $success;
    die();
}

//ajax response insert-update teacher
add_action('wp_ajax_utt_insert_update_teacher','utt_insert_update_teacher');
function utt_insert_update_teacher(){
    global $wpdb;
    //data
    $firstname=$_GET['teacher_name'];
    $lastname=$_GET['teacher_surname'];
    $teacherid=$_GET['teacher_id'];
    $maxhour=$_GET['teacher_max_work'];
    $minhour=$_GET['teacher_min_work'];
    $teacherSubject=$_GET['teacher_subject_id'];
    $teacherGroup=$_GET['teacher_group_id'];
    $teachersTable=$wpdb->prefix."utt_teachers";
    //insert
    if($teacherid==0){
        $safeSql = $wpdb->prepare("INSERT INTO $teachersTable (name, surname, minWorkLoad, maxWorkLoad, assignedWorkLoad, subjectID, groupID) VALUES (%s,%s,%d,%d,0,%d,%d)",$firstname,$lastname,$minhour,$maxhour,$teacherSubject,$teacherGroup);
        $success = $wpdb->query($safeSql);
        if($success == 1){
            //success
            echo 1;
        }else{
            //fail
            echo 0; 
        }
    //edit
    }else{
        $safeSql = $wpdb->prepare("UPDATE $teachersTable SET name=%s, surname=%s, minWorkLoad=%d, maxWorkLoad=%d subjectID=%d, groupID=%d WHERE teacherID=%d; ",$firstname,$lastname,$minhour,$maxhour,$teacherSubject,$teacherGroup,$teacherid);
        $success = $wpdb->query($safeSql);
        if($success == 1){
            //success
            echo 1;
        }else{
            //fail
            echo 0;
        }
    }
die();
}

?>