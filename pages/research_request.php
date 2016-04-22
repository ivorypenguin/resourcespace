<?php
include_once "../include/db.php";
include_once "../include/general.php";
include_once "../include/authenticate.php";
include_once "../include/research_functions.php";

$name        = getval('name', '');
$email       = getval('email', '');
$description = getval('description', '');

if (getval("save","") != "")
    {
    $errors=false;
    if ($name == "") {$errors=true;$error_name=true;}
    if ($description == "") {$errors=true;$error_description=true;}
    if (isset($anonymous_login) && $anonymous_login==$username && $email == "") {$errors=true;$error_email=true;}
    if ($errors == false) 
        {
        # Log this
        daily_stat("New research request",0);

        send_research_request();
        redirect($baseurl_short."pages/done.php?text=research_request");
        }
    }

include "../include/header.php";
?>

<div class="BasicsBox">
    <h1><?php echo $lang["researchrequest"]?></h1>
    <p class="tight"><?php echo text("introtext")?></p>
    <p class="greyText noPadding">* <?php echo $lang["requiredfield"]?></p>
    <?php if (!hook('replace_research_request_form')) { ?>
    <form method="post" action="<?php echo $baseurl_short?>pages/research_request.php">

        <?php if (getval("assign","")!="") { ?>
        <div class="Question">
            <label><?php echo $lang["requestasuser"]?></label>
            <select name="as_user" class="stdwidth">
                <?php
                $users=get_users(0,"","u.username",true);
                for ($n=0;$n<count($users);$n++)
                {
                    ?><option value="<?php echo $users[$n]["ref"]?>"><?php echo $users[$n]["username"] . " - " . $users[$n]["fullname"] . " ("  . $users[$n]["email"] . ")"?></option>
                    <?php
                }
                ?>
            </select>
            <div class="clearerleft"></div>
        </div>
        <?php } ?>

        <div class="Question">
            <label for="name"><?php echo $lang["nameofproject"]?> *</label>
            <input id="name" name="name" class="stdwidth" value="<?php echo htmlspecialchars($name) ?>">
            <div class="clearerleft"></div>
            <?php if (isset($error_name)) { ?><div class="FormError"><?php echo $lang["noprojectname"]?></div><?php } ?>
        </div>

        <div class="Question">
            <label for="description">
                <?php echo $lang["descriptionofproject"]?> *<br/>
                <span class="OxColourPale"><?php echo $lang["descriptionofprojecteg"]?></span>
            </label>
            <textarea id="description" rows="5" cols="50" name="description" class="stdwidth"><?php echo htmlspecialchars($description) ?></textarea>
            <div class="clearerleft"></div>
            <?php if (isset($error_description)) { ?><div class="FormError"><?php echo $lang["noprojectdescription"]?></div><?php } ?>
        </div>

        <div class="Question">
            <label for="deadline"><?php echo $lang["deadline"]?></label>
            <select id="deadline" name="deadline" class="stdwidth">
                <option value=""><?php echo $lang["nodeadline"]?></option>
                <?php 
                for ($n=0;$n<=150;$n++)
                    {
                    $date = time()+(60*60*24*$n);
                    $d    = date("D",$date);
                    $option_class = '';
                    if (($d == "Sun") || ($d == "Sat"))
                        {
                        $option_class = 'optionWeekend';
                        } ?>
                    <option class="<?php echo $option_class ?>" value="<?php echo date("Y-m-d",$date)?>"><?php echo nicedate(date("Y-m-d",$date),false,true)?></option>
                    <?php
                    } ?>
            </select>
            <div class="clearerleft"></div>
        </div>

        <?php if (isset($anonymous_login) && $anonymous_login == $username) { 
            # Anonymous access - we need to collect their e-mail address.
            ?>
            <div class="Question" id="email">
                <label for="email"><?php echo $lang["email"]?></label>
                <input id="email" name="email" class="stdwidth" value="<?php echo htmlspecialchars($email) ?>">
                <div class="clearerleft"> </div>
                <?php if (isset($error_email)) { ?><div class="FormError"><?php echo $lang["setup-emailerr"]?></div><?php } ?>
            </div>
        <?php } ?>

        <div class="Question" id="contacttelephone">
            <label for="contact"><?php echo $lang["contacttelephone"]?></label>
            <input id="contact" name="contact" class="stdwidth" value="<?php echo htmlspecialchars(getval("contact","")) ?>">
            <div class="clearerleft"></div>
        </div>

        <div class="Question">
            <label for="finaluse">
                <?php echo $lang["finaluse"]?><br/>
                <span class="OxColourPale"><?php echo $lang["finaluseeg"]?></span>
            </label>
            <input id="finaluse" name="finaluse" class="stdwidth" value="<?php echo htmlspecialchars(getval("finaluse","")) ?>">
            <div class="clearerleft"></div>
        </div>

        <div class="Question" id="resourcetype">
            <label><?php echo $lang["resourcetype"]?></label>
            <div class="tickset lineup">
                <?php 
                $types = get_resource_types();
                for ($n=0;$n<count($types);$n++) 
                    { ?>
                    <div class="Inline">
                        <input id="TickBox" type="checkbox" name="resource<?php echo $types[$n]["ref"]?>" value="yes" checked>
                        &nbsp;<?php echo htmlspecialchars($types[$n]["name"])?>
                    </div>
                <?php } ?>
            </div>
            <div class="clearerleft"></div>
        </div>

        <div class="Question" id="noresourcesrequired">
            <label for="noresources"><?php echo $lang["noresourcesrequired"]?></label>
            <input id="noresources" name="noresources" class="shrtwidth" value="<?php echo htmlspecialchars(getval("noresources",""))?>">
            <div class="clearerleft"></div>
        </div>

        <div class="Question" id="shaperequired">
            <label for="shape"><?php echo $lang["shaperequired"]?></label>
            <select id="shape" name="shape" class="stdwidth">
                <option><?php echo $lang["portrait"]?></option>
                <option><?php echo $lang["landscape"]?></option>
                <option selected><?php echo $lang["either"]?></option>
            </select>
            <div class="clearerleft"></div>
        </div>

        <?php if (file_exists(dirname(__FILE__) . "/../plugins/research_request.php")) { include dirname(__FILE__) . "/../plugins/research_request.php"; } ?>


        <div class="QuestionSubmit">
            <label for="buttons"> </label>          
            <input name="save" type="submit" value="&nbsp;&nbsp;<?php echo $lang["sendrequest"]?>&nbsp;&nbsp;" />
        </div>

    </form>
    <?php } # end hook('replace_research_request_form') ?>
</div>

<?php
include "../include/footer.php";
?>
