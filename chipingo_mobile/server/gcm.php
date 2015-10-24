<?php

    /**
     * Example: 
     *   $reg_id = [ "APA91bE7fippO_bRWVG0XT5EGVWDEsu_kIAUiDPZUv2otav78Y1AainGgWfdSuMDW-k8k2WLcgK99kPT3MKOE-M6tyTCNkeFDHwQp2ROxsomDrOd2iOPA3FYsDb6ys1zdWss57k4xsNE" ];
     *   $msg = "Google Cloud Messaging working well";
     *   send_gcm_notify($reg_id, $msg);
     * 
     * @param array $reg_id
     * @param text $message
     * @return text result 
     *    String of gcm server
     */
    define("GOOGLE_API_KEY", "AIzaSyAk_yjvZyKKYh0UcHObnWfMW-nwsa3rpdc");
    define("GOOGLE_SENDER_ID", "523422180998" );
    define("GOOGLE_GCM_URL", "https://android.googleapis.com/gcm/send");
    function send_gcm_notify($reg_id, $message) {
      
      if (is_null($reg_id)) {
        $reg_id = [
          "APA91bHfV-lCW7hjxX0uKsEDpl7e3CFn8mvFrpK7NPshEHSrxoKEc35OEKWlcp-s1DorEhuGGJcpjnE7r92ryNlnAtJXmn2OT2VBZcnyV1IH_B9dTM7x-yKcvAXcJB2MY43fu6uaPan_",
          //"APA91bFZci_pNw60vKhpkwNGYd8DGJ7vr3J5oEOwt4LXf-pcZLXi8tyERNThUXkOzu7Z1hrXPav3QvP019MuuvTSvrStBgxpAM8doKMflM4e_v9FWFUYDUo_cOyttuMyu6Fr7J7EK5kV"
          ];
      }
      
      $fields = array(
          'registration_ids'  => $reg_id,
          'data'              => array( "message" => $message ),
      );
      $headers = array(
          'Authorization: key=' . GOOGLE_API_KEY,
          'Content-Type: application/json'
      );
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, GOOGLE_GCM_URL);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
      $result = curl_exec($ch);
      if ($result === FALSE) {
          die('Problem occurred: ' . curl_error($ch));
      }
      curl_close($ch);
      return $result;
    }