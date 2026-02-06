// Set the recipient email address.
        // FIXME: Update this to your desired email address.

        $recipient = "info@evergreenautoinsurance.com,mahabulislam86@gmail.com,evergreenautoinsurance@gmail.com";
        // $recipient = "ashrafuddin765@gmail.com";



        // Set the email subject.
        $subject = $mail_subject;



        // Build the email content.
        if($name){
            $email_content = "Name: $name\n";        
        }
        if($referers_name){
            $email_content = "Referrer's Name: $referers_name\n";        
        }
        if($email){
            $email_content .= "Email: $email\n\n";        
        }
        if($referers_email){
            $email_content .= "Referrer's Email: $referers_email\n";        
        }
        if($phoneNo){
            $email_content .= "Phone Number: $phoneNo\n";        
        }
        if($referers_phoneNo){
            $email_content .= "Referrer's Phone Number: $referers_phoneNo\n\n";        
        }
        if($insuranceType){
        $email_content .= "Insurance Type: $insuranceType\n";        
        }

        if($joinCat){
            $email_content .= "Field of Interest/Expertise: $joinCat\n";        
        }
        if($prevExp){
            $email_content .= "Previous Work Experience?(if any): $prevExp\n";        
        }
        if($whyYouWant){
            $email_content .= "Why You Want to Join Us?: $whyYouWant\n";        
        }
        if($message){
            $email_content .= "Message:\n$message\n";        
        }

       if($owner_occupied){
            $email_content .= "Is this Owner Occupied or Rented out? : $owner_occupied\n";        
        }

        if($date){
            $email_content .= "Date: $date\n";        
        }
        if($wtoBUsines){
            $email_content .= "What type of Business is this?: $wtoBUsines\n";        
        }
        if($corp_name){
            $email_content .= "Corporation Name: $corp_name\n";        
        }
        if($dba){
            $email_content .= "DBA: $dba\n";        
        }
        if($bpAddress){
            $email_content .= "Business Property Address: $bpAddress\n";        
        }
        if($city){
            $email_content .= "City, State & Zip: $city\n";        
        }
        if($owner_name){
            $email_content .= "Owner's Name: $owner_name\n";        
        }
        if($owner_email){
            $email_content .= "Owner's Email: $owner_email\n";        
        }
        if($owner_cell){
            $email_content .= "Owner's Cell No: $owner_cell\n";        
        }
        if($btCall){
            $email_content .= "Best Time to call: $btCall\n";        
        }

        // Build the email headers.
        $email_headers = "From: $name <$email>";
        $email_headers ="MIME-Version: 1.0 ";
        $email_headers.="Content-type: text/html;charset=utf-8 ";
        $email_headers.="X-Priority: 3";
        // $email_headers.="X-Mailer: smail-PHP ".phpversion()."";
        // Send the email.
        if (mail($recipient, $subject, $email_content, $email_headers)) {
            // Set a 200 (okay) response code.
            http_response_code(200);
            echo "Thank You! Your message has been sent.";
            exit();
        } else {

            // Set a 500 (internal server error) response code.
            http_response_code(500);
            echo "Oops! Something went wrong and we couldn't send your message.";
        }



    } else {
        // Not a POST request, set a 403 (forbidden) response code.
        http_response_code(403);
        echo "There was a problem with your submission, Please try again.";
    }



?>
