<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="icon" href="images/KTVD-icon-small.png">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
BODY {
    font-family: Times New Roman,serif;
    font-size: 13px;
    max-width: 900px;
}

.lang-vi {
    color: #080;
    padding: 0px 12px 0px 12px;
}

.lang-en {
    padding: 0px 12px 0px 12px;
    color: #258;
    font-style: italic;
}

.letterhead {
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    justify-content: space-between;
    border-bottom: 1px solid #888;
}

.headings {
    display: inline-block;
}

.headings .giaoxu-name {
    text-align: center;
    font-family: Arial,sans-serif;
    font-weight: bold;
    color: #245;
    font-size: 16px;
    line-height: 18px;
}

.headings .parish-name {
    text-align: center;
    font-family: Arial,sans-serif;
    font-weight: bold;
    color: #245;
    font-size: 16px;
    line-height: 18px;
}

.headings .school-name {
    text-align: center;
    font-size: 16px;
    font-weight: 600;
    line-height: 18px;
    color: #a00;
}

.headings .address {
    text-align: center;
    font-family: Arial,sans-serif;
    font-weight: 300;
    font-size: 14px;
    line-height: 18px;
    color: #245;
}

TABLE {
    margin-top: 20px;
    border-collapse: collapse;
}

.family {
}

TD {
    padding: 0px 5px 0px 5px;
}

.family TH,
.payment TH {
    width: 80px;
    text-align: right;
    color: #357;
    font-family: Arial,sans-serif;
    font-size: 12px;
    white-space: nowrap;
}

.students {
}

.students TH {
    font-family: Arial,sans-serif;
    font-size: 12px;
    color: #357;
    white-space: nowrap;
}

.students .emphasis {
    color: #a00;
}

.students TR {
    border-bottom: 1px solid #888;
}

.students TD {
}

.students TD.fee,
.students TD.id {
    text-align: center;
}

.students TD.gl {
    text-align: center;
    font-family: Arial,Helvetica,sans-serif;
    font-size: 10px;
    font-weight: bold;
    color: #00a;
}

.indicator {
    text-align: center;
    font-size: 20px;
    line-height: 20px;
}

.signup {
    text-align: center;
}

.date {
    text-align: right;
    white-space: nowrap;
}

.title {
    font-size: 24px;
    font-weight: 700;
    text-align: center;
    color: #26a;
}

.subtitle {
    font-size: 20px;
    font-style: italic;
    font-weight: 700;
    text-align: center;
    color: #245;
}

.amount {
    text-align: right;
}

.payment TR.first-detail {
    border-top: 1px solid #888;
}

.payment-detail TH,
.payment-detail TD {
    font-weight: 300;
}

.waiver {
    margin-top: 20px;
    width: 750px;
}

.needs {
    margin-top: 20px;
    width: 750px;
}

.needs span {
}

.family td.correction {
    border: 1px solid #a00;
    min-width: 400px;
    text-align: center;
    vertical-align: top;
    font-weight: bold;
    font-family: Arial,Helvetica,Sans-Serif;
    color: #a00;
}

.acceptance {
    margin-top: 30px;
    font-family: Arial,Helvetica,Sans-Serif;
}

.acceptance .signature {
    display: inline-block;
    width: 350px;
    border-bottom: 1px solid black;
}

.acceptance .signdate {
    display: inline-block;
    width: 150px;
    border-bottom: 1px solid black;
}

.tuition {
    margin-top: 20px;
    width: 780px;
}

.caption {
    font-weight: bold;
}

.doublespace {
    height: 40px;
}

.total {
    margin-top: 10px;
}

.total .label {
    display: inline-block;
    width: 50px;
    font-weight: bold;
    color: #a00;
    text-align: right;
    vertical-align: top;
    padding-top: 10px;
}

.total .minage {
    display: inline-block;
    color: #a00;
    vertical-align: top;
    padding-top: 10px;
}

.total .amount {
    display: inline-block;
    width: 80px;
    height: 25px;
    border: 1px solid #a00;
}

.regform {
    page-break-after: always;
    margin: 40px;
}

.period {
    text-align: right;
    font-family: Arial,sans-serif;
    font-size: 12px;
    font-weight: bold;
    color: black;
    margin-right: 10px;
}

</style>
</head>
<body>

<?php
require_once("config.php");
require_once("utils.php");

$details = @alt($_POST['details'], $_GET['details']);
$nologo = @alt(intval($_POST['nologo']), intval($_GET['nologo']));
$period = strtolower(@alt($_POST['period'], $_GET['period'], 'gl'));

$dbc = school_db();

$settings = array();
$stm = $dbc->query("select * from settings where deprecated is null");
while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['attr']] = $row['value'];
}
$stm->closeCursor();
$settings['senior_student_birthyear'] = $settings['registration_year']  - $settings['senior_student_age'];
$settings['graduate_birthyear'] = $settings['registration_year']  - $settings['graduate_age'];

//
function yes_no($t) 
{
    $t = "N"; // strtoupper($t);
    return $t[0] == 'Y' ? '&#x2611;' : ($t[0] == 'N' ? '&#x2610;' : '&#xfe56;');
    //return $t[0] == 'U' ? '?' : $t[0]; //($t[0] == 'N' ? '&#x20e0;' : '?');
}

//
function tuition($bd)
{
    global $settings;
    
    if (!$bd) {
        return "\$$settings[first_student_fee]/\$$settings[next_student_fee]";
    }

    $t = strtotime($bd);
    $year = date("Y", $t);
    if ($year <= $settings['graduate_birthyear']) {
        return "";
    }
    if ($year <= $settings['senior_student_birthyear']) {
        return "\$$settings[senior_student_fee]";
    }
    return "\$$settings[first_student_fee]/\$$settings[next_student_fee]";
}

//
$fids = array();
$stm = $dbc->prepare("select fid,vn,gl,tn from student left join schedule on student.id = schedule.id where signup = 'Y' order by birthdate desc");
$stm->execute();
while ($row = lowkey($stm->fetch(PDO::FETCH_ASSOC))) {
    $fids[$row['fid']] = $row[$period]; // save only the classname of the oldest student in each family
}
$stm->closeCursor();
asort($fids);


$rooms = array();
$rset = $dbc->query("select period,class,room from room");
while ($row = lowkey($rset->fetch(PDO::FETCH_ASSOC))) {
    $rooms["$period-$row[class]"] = $row['room'];
}

function classroom($cls) {
    global $period, $rooms;
    if (isset($rooms["$period-$cls"])) {
        $rno = $rooms["$period-$cls"];
        if ($rno) {
            return "$cls#$rno";
        }
    }
    return $cls;
}

//
//
//
foreach ($fids as $fid=>$cls) {

    echo "<div class='regform'>";    

    $stm = $dbc->prepare("select * from family where fid = ?");
    echo "<div id='receipt'>";
    echo "<div class='letterhead'>";
    if (!$nologo) {
        echo "<img src='images/KTVD-official-logo.png' width='76' height='76'>";
    }
    echo "<div class='headings'>";
    echo "<div class='giaoxu-name'>Giáo Xứ Nữ Vương Các Thánh Tử Đạo Việt Nam</div>";
    echo "<div class='parish-name'>Queen of Vietnamese Martyrs Catholic Church</div>";
    echo "<div class='school-name'>Đoàn TNTT Kitô Vua &ndash; Trường GL/VN Đaminh Savio</div>";
    echo "<div class='address'>4655 Harlan St. &#x25aa; Wheat Ridge, CO 80033 &#x25aa; 303-431-0382</div>";
    echo "</div>";
    if (!$nologo) {
        echo "<img src='images/DaminhSavioLogo.png' width='80' height='80'>";
    }
    echo "</div>";
    echo "<div class='period'>".strtoupper($period).":".classroom($cls)."</div>";
    echo "<div class='title'>Ghi Danh / Registration 2022-2023</div>";
    echo "</div>";

    /*
    if (isset($args) && $argc >= 3) {
    // bypass auth
    }
    else if (!($cred = see_family($fid))) {
        echo "<h2 class='error'>Unauthorized</h2>";
        exit(1);
    }
    */

    $stm->execute(array($fid));
    $fam = lowkey($stm->fetch(PDO::FETCH_ASSOC));
    $stm->closeCursor();

    echo "<table class='family'>";
    echo "<tr>";
    echo "<td>";
    echo "<table>";
    echo "<tr><th>Env.<i>#FID</i>: </th><td><b>$fam[dno]</b> #<i>$fam[fid]</i></td></tr>";
    echo "<tr><th>Parent:</th><td>$fam[father] &amp; $fam[mother]</td></tr>";
    echo "<tr><th>Address:</th><td>$fam[address], $fam[city], $fam[state] $fam[zipcode]</td></tr>";
    echo "<tr><th>Phones:</th><td>".phone_number($fam['phone1']).phone_number($fam['phone2'],',').phone_number($fam['phone3'],',')."</td></tr>";
    echo "<tr><th>Email:</th><td>$fam[email]</td></tr>";
    echo "</table>";
    echo "</td>";
    echo "<td class='correction'>";
    echo "Thay Đổi Địa Chỉ, Điện Thoại / Update Address, Phone";
    echo "</td>";
    echo "</tr>";
    echo "</table>";


    $stm = $dbc->prepare("select * from student left join schedule on student.id = schedule.id where fid = ? order by birthdate");
    $stm->execute(array($fid));
    $empty_rec = array_merge(make_map(get_query_columns($stm), ''), array('id'=>'-'));

    echo "<table class='students'>";
    echo "<tr>";
    echo "<th>Ghi Danh /<br>Signup</th>";
    echo "<th>Học Phí /<br>Fee</th>";
    echo "<th>ID</th><th>Tên /<br>Name</th>";
    echo "<th>Sinh Nhật /<br>Birthdate</th>";
    //echo "<th>GL</th>";
    echo "<th>Rửa Tội /<br>Baptism</th>";
    echo "<th>Xưng Tội /<br>Confession</th>";
    echo "<th>Rước Lễ /<br>Eucharist</th>";
    echo "<th>Thêm Sức /<br>Confirmation</th>";
    echo "<th class='emphasis'>Nhu Cầu Đặc Biệt/<br>Special Needs*</th>";
    echo "</tr>";
    $signups = 0;

    $students = $stm->fetchall(PDO::FETCH_ASSOC);

    // 
    $students[] = array_merge(array(), $empty_rec);
    $students[] = array_merge(array(), $empty_rec);

    foreach ($students as $std) {
        $student = lowkey($std);
        echo "<tr class='".($student['id']=="-"?"doublespace":"single")."'>";
        $signup = ($student['signup'] == 'Y');
        $signups += $signup ? 1 : 0;
        $fee = tuition($student['birthdate']);
        echo "<td class='indicator'>".($fee!=''? "&#x2610;" : "")."</td>";
        echo "<td class='fee'>$fee</td>";
        echo "<td class='id'>$student[id]</td>";
        echo "<td class='name'>".student_fullname($student)."</td>";
        echo "<td class='date'>".desc_date($student['birthdate'])."</td>";
        // echo "<td class='gl'>".classroom($student[$period])."</td>";
        echo "<td class='indicator'>".yes_no($student['baptized'])."</td>";
        echo "<td class='indicator'>".yes_no($student['confession'])."</td>";
        echo "<td class='indicator'>".yes_no($student['communion'])."</td>";
        echo "<td class='indicator'>".yes_no($student['confirmed'])."</td>";
        echo "<td class='indicator emphasis'>&#x2610;</td>";
        echo "</tr>";
    }
    echo "</table>";

    $senior_year = $settings['registration_year'] - $settings['senior_student_age'];
    $min_year = $settings['registration_year'] - $settings['min_age'];

    echo "<div class='total'>";
    echo "<span class='label'>Total:&nbsp;</span><span class='amount'></span>";
    echo "</div>";
    echo "<p/>";

    echo "<div class='caption'>* Tuổi Học Sinh / Age Requirement</div>";
    echo "<span class='lang-vi'>Học sinh phải sinh TRƯỚC $settings[cutoff_date]-$min_year.</span><br><span class='lang-en'>Student must be born BEFORE $settings[cutoff_date]-$min_year.</span>";
    echo "<p/>";

    echo "<div class='caption'>* Hồ Sơ Cần Thiết / Document Requirement</div>";
    echo "<span class='lang-vi'>Khi ghi danh học sinh mới, xin mang theo giấy Rửa Tội hoặc giấy khai sinh.</span><br><span class='lang-en'>First time student, please provide copy of Baptism certificate or birth certificate.</span>";
    echo "<p/>";

    echo "<div class='caption'>* Học Phí/Tuition</div>";
    echo "<span class='lang-vi'>Học phí cho những em sinh trong hoặc trước năm $senior_year là \$$settings[senior_student_fee]. Sau đó, học phí cho em thứ nhất là \$$settings[first_student_fee]; các em sau là \$$settings[next_student_fee].</span><br><span class='lang-en'>Tuition for student born on or before $senior_year is \$$settings[senior_student_fee]; Thereafter, the first student&apos;s fee is \$$settings[first_student_fee] and subsequent students are \$$settings[next_student_fee].</span>";
    echo "<p/>";

    echo "<div class='caption'>* Nhu Cầu Đặc Biệt/Special Needs Student</div>";
    echo "<span class='lang-vi'>Trước khi ghi danh cho những em cần sự chăm sóc đặc biệt, xin quý phụ huynh nói chuyện với Cha Tuyên Úy hoặc thầy hiệu trưởng để hiểu rõ hoàn cảnh.<span><br><span class='lang-en'>Before signing up for students who need special care, please talk to the Chaplain or the principal to understand if the school is qualified to serve your child(ren).</span>";
    echo "<p/>";

    echo "<div class='waiver'>";
    echo "<div class='subtitle'>Miễn Trách Nhiệm / Liability Waiver</div>";
    echo "<span class='lang-vi'>Tôi chấp thuận cho con em tôi tham dự trường học Giáo Xứ Nữ Vương Các Thánh Tử Đạo Việt Nam và Đoàn Thiếu Nhi Thánh Thể Kitô Vua-Denver. Tôi sẽ chỉ thị cho con em tôi phải tuân giữ các điều luật của trường và tuân theo sự hướng dẫn của những người phụ trách. Tôi miễn cho Giáo Xứ, và những người làm việc cho giáo xứ, mọi trách nhiệm đối với những gì có thể xảy ra cho con em tôi lúc ở trong hoặc ngoài khuôn viên giáo xứ. Tôi cũng đồng ý cho giáo xứ dùng các hình ảnh sinh hoạt của con em tôi trong các ấn loát và truyền thông của giáo xứ và Phong Trào.</span>";
    echo "<span class='lang-en'>I agree to let my child(ren) attend the Queen of Vietnamese Martyrs Parochial School and the Denver Chapter of the Vietnamese Eucharistic Youth Movement. I will instruct my child(ren) to obey all the rules of the school and to follow the instructions of those in charge. I hereby release the parish, and those acting on behalf of the parish, from all liability for what may happen to my child(ren) while on or off parish premises. I also agree to let the parish use any photo/video of my child(ren) activities for publication and promotional materials.</span>";
    echo "</div>";

    echo "<div class='acceptance'><span>Ký Tên/Signature: </span><span class='signature'></span> <span>Ngày/Date: </span><span class='signdate'></span></div>";
    echo "</div>";
    echo "</div>";
}
?>
</body>
</html>
