<?php

//include js
function utt_group_scripts() {
    //include groupScripts
    wp_enqueue_script( 'groupScripts', plugins_url( 'js/groupScripts.js', __FILE__ ) );
    //localize groupScripts
    wp_localize_script( 'groupScripts', 'groupStrings', array(
        'deleteForbidden' => __( 'Delete is forbidden while completing the form!', 'UniTimetable' ),
        'deleteGroup' => __( 'Are you sure that you want to delete this Group?', 'UniTimetable' ),
        'groupDeleted' => __( 'Group deleted successfully!', 'UniTimetable' ),
        'groupNotDeleted' => __( 'Failed to delete Group. Check if Group is used by a Lecture.', 'UniTimetable' ),
        'editForbidden' => __( 'Edit is forbidden while completing the form!', 'UniTimetable' ),
        'editGroup' => __( 'Edit Group', 'UniTimetable' ),
        'cancel' => __( 'Cancel', 'UniTimetable' ),
        'periodVal' => __( 'Please select a Period.', 'UniTimetable' ),
        'semesterVal' => __( 'Please select Semester.', 'UniTimetable' ),
        'nameVal' => __( 'Please avoid using special characters and do not use long names.', 'UniTimetable' ),
        'insertGroup' => __( 'Insert Group', 'UniTimetable' ),
        'reset' => __( 'Reset', 'UniTimetable' ),
        'group' => __( 'Group', 'UniTimetable' ),
        'failAdd' => __( 'Failed to add Groups. Check if there are another Groups with the same attributes.', 'UniTimetable' ),
        'successAdd' => __( 'Groups added successfully!', 'UniTimetable' ),
        'failEdit' => __( 'Failed to edit Group. Check if there is another Group with the same attributes.', 'UniTimetable' ),
        'successEdit' => __( 'Group successfully edited!', 'UniTimetable' ),
    ) );
}

//groups page
function utt_create_groups_page() {
//group form
    ?>
    <div class="wrap">
        <h2 id="groupTitle"> <?php _e( "Insert Group", "UniTimetable" ); ?> </h2>
        <form action="" name="groupForm" method="post">
            <input type="hidden" name="groupID" id="groupID" value=0 />
            <div class="element firstInRow">
            </div>
            <div class="element">
                <!-- select number of groups to be created -->
                <?php _e( "Number of Groups:", "UniTimetable" ); ?><br/>
                <select name="groupsNumber" id="groupsNumber" class="dirty">
                    <?php
                    for ( $i = 1; $i < 16; $i++ ) {
                        echo "<option value=$i>$i</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="element firstInRow groupsName">
                <?php _e( "Name of Groups (Prefix):", "UniTimetable" ); ?><br/>
                <!-- prefix of groups' names -->
                <input type="text" name="groupsName" id="groupsName" class="dirty" value="<?php _e( "Group", "UniTimetable" ); ?>"/>
            </div>
            <div class="element counterStart">
                <?php _e( "Counter Starτ:", "UniTimetable" ); ?>
                <!-- starting number of groups that will be created -->
                <select name="counterStart" id="counterStart" class="dirty">
                    <?php
                    for ( $i = 1; $i < 16; $i++ ) {
                        echo "<option value=$i>$i</option>";
                    }
                    ?>
                </select>
            </div>
            <div id="secondaryButtonContainer">
                <input type="submit" value="<?php _e( "Submit", "UniTimetable" ); ?>" id="insert-updateGroup" class="button-primary"/>
                <a href='#' class='button-secondary' id="clearGroupForm"><?php _e( "Reset", "UniTimetable" ); ?></a>
            </div>
        </form>
        <!-- place to view messages -->
        <div id="messages"></div>        
        <!-- place to view inserted groups -->
        <div id="groupsResults">
            <?php utt_view_groups(); ?>
        </div>
    </div>

    <?php
}

//ajax response view groups
add_action( 'wp_ajax_utt_view_groups', 'utt_view_groups' );

function utt_view_groups() {
    global $wpdb;
    $groupsTable = $wpdb->prefix . "utt_groups";
    //show registered groups
    //if not selected semester, show for all semesters

    $safeSql = $wpdb->prepare( "SELECT * FROM $groupsTable" );
    $groups = $wpdb->get_results( $safeSql );
    ?>
    <!-- show table of groups -->
    <table class="widefat bold-th">
        <thead>
            <tr>
                <th><?php _e( "Group", "UniTimetable" ); ?></th>
                <th><?php _e( "Actions", "UniTimetable" ); ?></th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th><?php _e( "Group", "UniTimetable" ); ?></th>
                <th><?php _e( "Actions", "UniTimetable" ); ?></th>
            </tr>
        </tfoot>
        <tbody>
            <?php
            //show grey and white records in order to be more recognizable
            $bgcolor = 1;
            foreach ( $groups as $group ) {
                if ( $bgcolor == 1 ) {
                    $addClass = "class='grey'";
                    $bgcolor = 2;
                } else {
                    $addClass = "class='white'";
                    $bgcolor = 1;
                }
                //add record
                echo "<tr id='$group->groupID' $addClass><td>$group->groupPrefix  $group->groupNo</td>
                <td><a href='#' onclick='deleteGroup($group->groupID);' class='deleteGroup'><img id='edit-delete-icon' src='" . plugins_url( 'icons/delete_icon.png', __FILE__ ) . "'/> " . __( "Delete", "UniTimetable" ) . "</a>&nbsp;
                <a href='#' onclick=\"editGroup($group->groupID, '$group->groupPrefix', $group->groupNo);\" class='editGroup'><img id='edit-delete-icon' src='" . plugins_url( 'icons/edit_icon.png', __FILE__ ) . "'/> " . __( "Edit", "UniTimetable" ) . "</a></td></tr>";
            }
            ?>
        </tbody>
    </table>
    <?php
    die();
}

//ajax response insert-update group
add_action( 'wp_ajax_utt_insert_update_group', 'utt_insert_update_group' );

function utt_insert_update_group() {
    global $wpdb;
    //data to be inserted/updated
    $groupID = $_GET['group_id'];
    $groupPrefix = $_GET['group_name'];
    $counterStart = $_GET['counter_start'];
    $groupsNumber = $_GET['groups_number'];
    $groupsTable = $wpdb->prefix . "utt_groups";
    $success = 0;
    // if groupID is 0, it is insert
    if ( $groupID == 0 ) {
        //transaction, so if an insert fails, it rolls back
        $wpdb->query( 'START TRANSACTION' );
        for ( $i = 1; $i <= $groupsNumber; $i++ ) {
            $safeSql = $wpdb->prepare( "INSERT INTO $groupsTable (groupPrefix, groupNo) VALUES (%s,%d)", $groupPrefix, $counterStart );
            $success = $wpdb->query( $safeSql );
            $counterStart ++;
            if ( $success != 1 ) {
                //if an insert fails, for breaks
                $success = 0;
                break;
            }
        }
        //if every insert succeeds, commit transaction
        if ( $success == 1 ) {
            $wpdb->query( 'COMMIT' );
            echo 1;
            //else rollback
        } else {
            $wpdb->query( 'ROLLBACK' );
            echo 0;
        }
        //it is edit
    } else {
        $safeSql = $wpdb->prepare( "UPDATE $groupsTable SET groupPrefix=%s  WHERE groupID=%d ", $groupPrefix, $groupID );
        $success = $wpdb->query( $safeSql );
        if ( $success == 1 ) {
            echo 1;
        } else {
            echo 0;
        }
    }
    die();
}

//ajax response delete group
add_action( 'wp_ajax_utt_delete_group', 'utt_delete_group' );

function utt_delete_group() {
    global $wpdb;
    $groupsTable = $wpdb->prefix . "utt_groups";
    $safeSql = $wpdb->prepare( "DELETE FROM `$groupsTable` WHERE groupID=%d", $_GET['group_id'] );
    $success = $wpdb->query( $safeSql );
    //if success is 1, delete succeeded
    echo $success;
    die();
}
?>