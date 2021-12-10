<?php

use WHMCS\Database\Capsule;

add_hook('AdminAreaViewTicketPageSidebar', 10, function($vars) {

    /** Set up vars */
    $WEBROOT = $GLOBALS['CONFIG']['SystemURL'];
    $ADMINURL = $WEBROOT . '/' . $GLOBALS['customadminpath'] . "/";

    $tickets_array = array();

    /* First Ticket Message */
    $attachments_str = Capsule::table('tbltickets')
            ->where('id', $vars['ticketid'])
            ->value('attachment');

    if (!empty($attachments_str)){
        $tickets_array[$vars['ticketid']] = explode('|', $attachments_str); //convert to array
    }

    /* Ticket Replies */
    foreach (Capsule::table('tblticketreplies')->select('id', 'attachment')->where('tid', $vars['ticketid'])->get() as $ticket_reply){
        if ( !empty($ticket_reply->attachment) ){
            $tickets_array[$ticket_reply->id] = explode('|', $ticket_reply->attachment); //convert to array
        }
    }

    $attachments_html = "<ul>";

    if ( empty($tickets_array) ){
        $attachments_html .= "<li>No attachments found for this ticket.</li>";
    }
    foreach ( $tickets_array as $ticketid => $attachments ){
        foreach ($attachments as $index => $filename){

            $att_id = substr($filename, 0, strpos($filename, '_')); //extract ID from filename
            $filename = substr($filename, strpos($filename, '_')+1); //remove ID from filename
            if (empty($filename)) $filename = "ID: $att_id";
            $att_url = "$WEBROOT/dl.php?type=ar&id=$ticketid&i=$index";

            $attachments_html .= "<li id='att$att_id'>";
            $attachments_html .= "<a href='#contentr$ticketid' class='pull-right'><i class=\"fas fa-comment-alt\"></i></a>";
            $attachments_html .= "&bull; <a href='$att_url'>$filename</a>";
            $attachments_html .= "</li>";
        }
    }
    
    $attachments_html .= "</ul>";

    $tal_output = '
<div class="sidebar-header">
    <i class="fas fa-paperclip"></i> Attachments
</div>
<div class="content-padded small" id="tal_content">';
    $tal_output .= $attachments_html;
    $tal_output .= '
</div>
<style>
#tal_content li a{ padding: 0; display:inline; }
#tal_content i.fa-comment-alt{ margin-top: 4.5px; font-size: 0.8em; color: #bbb; }
</style>';

    return $tal_output;

});