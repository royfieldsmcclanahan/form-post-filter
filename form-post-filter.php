<?php

function httpPost($url, $data)
{
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_REFERER, 'https://www.marketing-mojo.com/');
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

// TBD: Authentication Token
// check for a time-based generated token here.  it can be generated via javascript on our site page
// and kept wrapped in a dependency elsewhere, maybe even create a token in the database 
// on page load and read it here


/* This was overkill.  I'm going to make it work for the contact forms first and go from there

// These are the form definitions.  Follow the data layout here to add new forms.
$forms = [
    'HomePageContactUs' => [
        'ActionURL' => 'https://he380.infusionsoft.com/app/form/process/7b15a3527efdf4ce83087d3983aef109',
        'Fields' => [
            'inf_form_xid' => '7b15a3527efdf4ce83087d3983aef109',
            'inf_form_name' => 'Home Page - Contact Us Form Submitted',
            'infusionsoft_version' => '1.70.0.139007',
            'inf_field_FirstName' => '',
            'inf_field_LastName' => '',
            'inf_field_Email' => '',
            'inf_field_Company' => '',
            'inf_field_JobTitle' => '',
            'inf_field_Phone1' => '',
            'inf_custom_Comments' => '',
            'inf_custom_customleadsource' => 'contact_us_form'
        ]
    ],
    'ContactUs' => [
        'ActionURL' => 'https://he380.infusionsoft.com/app/form/process/2f84c74708bd50c472ce7ebc147ed3a2',
        'Fields' => [
            'inf_form_xid' => '2f84c74708bd50c472ce7ebc147ed3a2',
            'inf_form_name' => 'Contact Us Form Submitted',
            'infusionsoft_version' => '1.70.0.139007',
            'inf_field_FirstName' => '',
            'inf_field_LastName' => '',
            'inf_field_JobTitle' => '',
            'inf_field_Email' => '',
            'inf_field_Phone1' => '',
            'inf_custom_ReasonforContact0' => '',
            'inf_custom_Comments' => '',
            'inf_custom_customformfill' => 'contact_us_form'
        ]
    ]
];
 */

$form_name = strval($_POST['FormName']);

// Check to see if Home Page Contact Us form was specified as parameter, and if so pass data through to Infusionsoft
if ($form_name == 'HomePageContactUs')
{           
    // read the data from the form into variables
    
    $inf_form_xid = 'f9453fa26c844d18aeae9b7949993d26';
    $inf_form_name = 'Home Page - Contact Us Form Submitted (Replacement 2020)';
    $infusionsoft_version = '1.70.0.139007';
    $inf_field_FirstName = strval($_POST['inf_field_FirstName']);
    $inf_field_LastName = strval($_POST['inf_field_LastName']);
    $inf_field_Email = strval($_POST['inf_field_Email']);
    $inf_field_Company = strval($_POST['inf_field_Company']);
    $inf_field_JobTitle = strval($_POST['inf_field_JobTitle']);
    $inf_field_Phone1 = strval($_POST['inf_field_Phone1']);
    $inf_custom_Comments = strval($_POST['inf_custom_Comments']);
    
    // NOTE!! This field determins how Infusionsoft treats the form data through the use of conditional actions based on tagging.
    // It is usually blank, but when coming from certain other sources like Bing or Google search it may not be.  Watch out because it's not fully tested.
    $inf_custom_customleadsource = strval($_POST['inf_custom_customleadsource']);
    
    $valid_entry = true;
    
    $stripped_FirstName = strtolower(trim($inf_field_FirstName));
    $stripped_LastName = strtolower(trim($inf_field_LastName));
    $stripped_Email = strtolower(trim($inf_field_Email));
    $stripped_Company = strtolower(trim($inf_field_Company));
    $stripped_Comments = strtolower(trim($inf_custom_Comments));
    
    $domain_suffix = substr($stripped_Email, strpos($stripped_Email, '.'));
    
    if ($stripped_FirstName == $stripped_LastName  or
        $domain_suffix == '.ru' or
        $stripped_Company == 'google' or
        strpos($stripped_Comments, '<') !== false or
        strpos($stripped_Comments, '>') !== false or
        strlen($stripped_Comments) >= 900 or
        (substr_count($stripped_Comments, 'http:') + substr_count($stripped_Comments, 'https:')) > 2)
        {
            $valid_entry = false;
        }
        
    // log all form data for homepage contact us for now while testing necessity for Google Recaptcha 2021-02-03 RM
    $log = date("Y-m-d H:i:s") . PHP_EOL
            . "First Name: " . $inf_field_FirstName . PHP_EOL
            . "Last Name: " . $inf_field_LastName . PHP_EOL
            . "Email: " . $inf_field_Email . PHP_EOL
            . "Company: " . $inf_field_Company . PHP_EOL
            . "Job Title: " . $inf_field_JobTitle . PHP_EOL
            . "Phone: " . $inf_field_Phone1 . PHP_EOL
            . "Comments: " . $inf_custom_Comments . PHP_EOL
            . "Custom Lead Source: " . $inf_custom_customleadsource . PHP_EOL
            . "Blocked By Filter: " . ($valid_entry ? "No" : "Yes") . PHP_EOL . PHP_EOL;
    file_put_contents('/opt/bitnami/apps/wordpress/roylogs/homecontactform.log', $log, FILE_APPEND);
    
    if ($valid_entry)
    {
        // Use pass-through data to make a post to infusionsoft (if infusionsoft validates and kicks it back, the end user will not know.)
        httpPost(
            'https://he380.infusionsoft.com/app/form/process/f9453fa26c844d18aeae9b7949993d26',
            [
                'inf_form_xid' => $inf_form_xid,
                'inf_form_name' => $inf_form_name,
                'infusionsoft_version' => $infusionsoft_version,
                'inf_field_FirstName' => $inf_field_FirstName,
                'inf_field_LastName' => $inf_field_LastName,
                'inf_field_Email' => $inf_field_Email,
                'inf_field_Company' => $inf_field_Company,
                'inf_field_JobTitle' => $inf_field_JobTitle,
                'inf_field_Phone1' => $inf_field_Phone1,
                'inf_custom_Comments' => $inf_custom_Comments,
                'inf_custom_customleadsource' => $inf_custom_customleadsource
            ]
        );
    }
    
    //echo $inf_form_xid . '<br/>' . $inf_form_name . '<br/>' .  $infusionsoft_version  . '<br/>' . $inf_field_FirstName  . '<br/>' . $inf_field_LastName . '<br/>' .  $inf_field_Email . '<br/>' .  $inf_field_Company  . '<br/>' . $inf_field_JobTitle . '<br/>' .  $inf_field_Phone1 . '<br/>' .  $inf_custom_Comments;
    
    header('Location: https://www.marketing-mojo.com/thank-you?type=contact-home-page');
}

// Check to see if Top Menu Contact Us form was specified as parameter, and if so pass data through to Infusionsoft
if ($form_name == 'ContactUs')
{
    // read the data from the form into variables
    
    $inf_form_xid = '96729660405f885f39738817bb96fbaf';
    $inf_form_name = 'Contact Us Form Submitted';
    $infusionsoft_version = '1.70.0.139007';
    $inf_field_FirstName = strval($_POST['inf_field_FirstName']);
    $inf_field_LastName = strval($_POST['inf_field_LastName']);
    $inf_field_Email = strval($_POST['inf_field_Email']);
    $inf_field_Company = strval($_POST['inf_field_Company']);
    $inf_field_JobTitle = strval($_POST['inf_field_JobTitle']);
    $inf_field_Phone1 = strval($_POST['inf_field_Phone1']);
    $inf_custom_Comments = strval($_POST['inf_custom_Comments']);
    $inf_custom_ReasonforContact0 = strval($_POST['inf_custom_ReasonforContact0']);
    $inf_custom_customformfill = strval($_POST['inf_custom_customformfill']);
    
    
    $valid_entry = true;
    
    $stripped_FirstName = strtolower(trim($inf_field_FirstName));
    $stripped_LastName = strtolower(trim($inf_field_LastName));
    $stripped_Email = strtolower(trim($inf_field_Email));
    $stripped_Company = strtolower(trim($inf_field_Company));
    $stripped_Comments = strtolower(trim($inf_custom_Comments));
    
    $domain_suffix = substr($stripped_Email, strpos($stripped_Email, '.'));
    
    if ($stripped_FirstName == $stripped_LastName  or
        $domain_suffix == '.ru' or
        $stripped_Company == 'google' or
        strpos($stripped_Comments, '<') !== false or
        strpos($stripped_Comments, '>') !== false or
        strlen($stripped_Comments) >= 900 or
        (substr_count($stripped_Comments, 'http:') + substr_count($stripped_Comments, 'https:')) > 2)
        {
            $valid_entry = false;
        }
    
    if ($valid_entry)
    {
        // Use pass-through data to make a post to infusionsoft (if infusionsoft validates and kicks it back, the end user will not know.)
        httpPost(
            'https://he380.infusionsoft.com/app/form/process/96729660405f885f39738817bb96fbaf',
            [
                'inf_form_xid' => $inf_form_xid,
                'inf_form_name' => $inf_form_name,
                'infusionsoft_version' => $infusionsoft_version,
                'inf_field_FirstName' => $inf_field_FirstName,
                'inf_field_LastName' => $inf_field_LastName,
                'inf_field_Email' => $inf_field_Email,
                'inf_field_Company' => $inf_field_Company,
                'inf_field_JobTitle' => $inf_field_JobTitle,
                'inf_field_Phone1' => $inf_field_Phone1,
                'inf_custom_Comments' => $inf_custom_Comments,
                'inf_custom_ReasonforContact0' => $inf_custom_ReasonforContact0,
                'inf_custom_customformfill' => $inf_custom_customformfill
            ]
        );
    }
    
    //echo $domain_suffix . ' ' . $stripped_FirstName . ' ' . $stripped_LastName . ' ' . $stripped_Email . ' ' . $stripped_Company . ' ' . ($valid_entry ? 'valid' : 'not valid');
        
    //echo $inf_form_xid . '<br/>' . $inf_form_name . '<br/>' .  $infusionsoft_version  . '<br/>' . $inf_field_FirstName  . '<br/>' . $inf_field_LastName . '<br/>' .  $inf_field_Email . '<br/>' .  $inf_field_Company  . '<br/>' . $inf_field_JobTitle . '<br/>' .  $inf_field_Phone1 . '<br/>' .  $inf_custom_Comments;
    
    header('Location: https://www.marketing-mojo.com/thank-you?type=contact-us');
}