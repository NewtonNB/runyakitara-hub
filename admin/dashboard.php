<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
$db = getDBConnection();

// Get statistics
$stats = [];
$tables = ['lessons', 'dictionary', 'proverbs', 'articles', 'contact_messages'];
foreach ($tables as $table) {
    $result = $db->query("SELECT COUNT(*) as count FROM $table");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    $stats[$table] = $row['count'];
}

// Get recent messages
$rC);

// Get recent lesons
$recen);

// Get total users
$totalUsers = $db->query("SELECT COUNT(*) as count Ft'];

closeDBConnection($db);
?>
<!DOCT
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="0">
    <title>Dashboard - Runyakitara Hub Admin</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.goog">
    <link rel="preconnect" href="https://fonts.gstaigin>
    <link href="https://fonts.googleapis.com/css2?f>
    
    <!-- Bootstraps -->
    <link rel="s">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.nett>
    
    <link rel="stylesheet" href="../css/style.css">
    <link rel="styles
</head>
<body class="admin-body">
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class">
                <div class="logo">
                    <i class="bi bi-translate"></i>
                    <span>Runyakitara Hub</span>
                </div>
            </div>
            
            <nav class="admin-nav">
                <a hre">
                    <i class="bi bi-spe>
                    <span>Dashboard</
                </a>
                <a hre
                    <i class="bi bi-boo
                    <span>Lessons</span>
                </a>
                <a hre>
                  /i>
            
                </a>
                <a href="proverbs.php"-item">
                    <i class="bi bi-chat-quo>
                    <span>Proverbs</span>
                </a>
                <a href="articles.php" class="nav-item">
                    <i class="bi bi-newspaper"></i>
                    <sspan>
                </a>
               tem">
          "></i>
       /span>
       ): ?>
</html>

</body>
ript>
    </sc      });   }
               }
              }
            '
      n: 'bottompositio                        : {
   legend           
      ugins: {pl      
          se,atio: falAspectRinnta         mai       true,
 nsive:    respo            : {
 options           
 },              }]
           
  : 3idth     borderW             
  : '#fff',orCol      border        
         ],          
        0.8)'5, 222, 179,(24gba         'r              )',
  0.8, 116,a(212, 165        'rgb               .8)',
 , 133, 63, 0a(205    'rgb                  0.8)',
   19,9, 69,    'rgba(13              [
       r: kgroundColo      bac            ],
                     ; ?>
 ['articles']$statsecho ?php        <        ,
         erbs']; ?>prov $stats[' <?php echo                   ]; ?>,
    ionary''dictcho $stats[   <?php e           
          s']; ?>,ssons['leho $stat ec <?php                      
 : [    data           [{
      tasets:          da'],
      iclesArtroverbs', 'ary', 'PDiction, ' ['Lessons's: label              {
     data:    nut',
     gh  type: 'dou         tx, {
 tCew Chart(dis
        n);ontext('2d't').getCibutionChar('distrIdtByemennt.getEltx = docume distC   constt
      Charontributi// Dis        
        
        });        }
   
 }                       }
           }
                        sion: 0
  preci                        
    icks: {   t               
      ue,: trtZero      beginA         
            y: {        {
           scales:             ,
        }                }
              false
isplay:  d                     egend: {
         l        : {
        plugins             false,
Ratio: intainAspect  ma             e,
 ive: tru respons              
 options: {              },

                }]       dius: 8
     borderRa                : 2,
  idth    borderW                      ],
              )'
 45, 1, 82,rgba(160           '          , 1)',
   79, 1(245, 222gba 'r              
         6, 1)',5, 1116(212, rgba         '               , 63, 1)',
(205, 133  'rgba                      , 19, 1)',
, 69rgba(139   '                   lor: [
  borderCo                        ],
          )'
      .85, 00, 82, 4    'rgba(16                
    .8)',222, 179, 0245,      'rgba(                  
  0.8)',165, 116,212,     'rgba(                 0.8)',
   , 133, 63,  'rgba(205                      8)',
  19, 0. 69,9,ba(13       'rg                olor: [
 kgroundC  bac                
            ],    ?>
       ssages'];['contact_me $statsp echo?ph   <                  >,
   ']; ?rticlesstats['aecho $?php  <                    ; ?>,
   ['proverbs']tatsho $s <?php ec                 
      ry']; ?>,tiona'dics[o $stat <?php ech                       >,
ns']; ?lesso $stats['chohp e         <?p             ta: [
    da              ount',
     'Content C label:                 
  ts: [{se   data           sages'],
  'Mesticles', bs', 'Ar 'ProverDictionary',', 'essonslabels: ['L            ata: {
            d    r',
 'ba type:        
   ntCtx, {contehart(    new C');
    ntext('2dart').getCotChontenmentById('c.getEledocumenttentCtx =   const con
      hartrview Cnt Ove  // Conte   
          });
     ive');
    le('act.togg.classListsidebar')tor('.admin-erySelec document.qu          
 tion() {', func('clicktListenerEven?.addggle')idebarToId('stByement.getEl  documenggle
       sidebar tole // Mobi       ipt>

    <scr</div>
       
  </main>   
     </div>       v>
       </di            </a>
              
       te</span>bsin>View We    <spa               
     "></i>"bi bi-eye=  <i class                      -btn">
k-action"quiclass=l" cndex.htmhref="../i <a                 a>
         </       an>
       sp Messages</an>View       <sp                 e"></i>
envelops="bi bi-as     <i cl                
   ">tion-btnck-ac class="quis.php"f="message  <a hre            
      /a>    <        >
        </spanrticlespan>Add A           <     i>
        e"></us-circl bi-pli class="bi        <               tn">
 n-bctioss="quick-aclad" ction=adphp?as.ef="article    <a hr             
   </a>                
    overb</span>an>Add Prsp  <                      i>
circle"></ bi-plus-ass="bi     <i cl                  n-btn">
 -actiockuiass="q" clp?action=addoverbs.phhref="pr    <a                  </a>
             
      </span>Add Wordspan>   <                    /i>
 ircle"><-c"bi bi-plus= class     <i                   
">n-action-bt="quickd" class=adionp?actnary.ph="dictioref h        <a            
/a>         <
           >span</Add Lessonspan>           <             e"></i>
cls-ciri-plu"bi bass=    <i cl                   >
 ction-btn""quick-add" class==ationp?acessons.ph href="l  <a                  ">
-gridk-actionsass="quicdiv cl          <
      h3> Actions</i> Quickcharge"></-lightning-i biclass="b<i <h3>            
    fade-up">s="" data-aoons-sectiontik-ac"quic<div class=        
    Actions --><!-- Quick                    
  div>
       </        div>
        </
        div>   </                 ?>
 <?php endif;                 
       div>    </                   ; ?>
     endforeach    <?php                        /div>
           <                      ton>
    butl"></i></pencii-="bi bassclcon"><i lass="btn-in c<butto                                  /div>
           <                       v>
    </di                                      ?>
  ); at'])n['created_time($lessostrto Y',  d,te('Mphp echo da    <?                                       > • 
 ); ?el']ev($lesson['lho ucfirst?php ec <el:Lev                                        a">
    ivity-met"actclass=<div                                    /div>
     tle']); ?><tisson['rs($lespecialchatmlho h?php ecty-title"><ctivilass="a   <div c                                 t">
    -conten"activity=iv class <d                               
        </div>                            >
    ll"></iok-fibi bi-boss="i cla  <                                 
     con">ity-is="activlasv c        <di                           tem">
 "activity-idiv class=        <                       ): ?>
 sson $lesons asntLes$recep foreach (     <?ph                   ">
        ity-listivass="act  <div cl                      e: ?>
     <?php els                       </div>
                       
     >sons yet</p  <p>No les                            
  -book"></i>lass="bi bi c        <i                
        ty-state">ass="emp     <div cl                       >
: ?ns))tLessompty($recen?php if (e      <               ody">
   d-bs="car <div clas                  iv>
      </d         
      /i></a>right"><rrow-i-a"bi bi class= <ll">View Allss="view-a.php" claessons <a href="l                    s</h3>
   ecent Lesson"></i> R"bi bi-book<i class=3>       <h              ader">
   s="card-he clas<div               
     ay="200">os-del-a" dataupaos="fade-data-ard" "activity-c<div class=            
                    div>
  </          
    iv>  </d            ?>
       endif; <?php                 
       /div>    <                      h; ?>
  reacp endfoph     <?                     >
            </div                        
        </div>                         ?>
      ']);atussg['stirst($mcho ucfp e  <?ph                                   ">
   ?>status']; [' $msgs <?php echoatuity-sts="activiv clas          <d                      div>
    </                                 >
     </div                                      ?>
 d_at']));sg['create$mrtotime(, Y', st('M dho date?php ec        <                              • 
      ); ?> g['name']s($msharlspecialcho htmom: <?php ec         Fr                            
       vity-meta">ass="acti<div cl                                    </div>
    bject']); ?>($msg['sulcharshtmlspecia><?php echo e"itl"activity-tass=    <div cl                                    ontent">
-ctivityss="ac cla   <div                            >
     div        </                           </i>
 pe">nvelobi bi-eclass="<i                                        >
 on"ity-icss="activ <div cla                                  em">
 ivity-itctlass="a   <div c                        ?>
      as $msg):entMessages ch ($rechp forea  <?p                             list">
 vity-"actilass=     <div c                  ?>
     php else:         <?                
 </div>                     
      s yet</p>>No message       <p                      </i>
   ox">bi bi-inbass=" cl   <i                      ">
       atey-stlass="empt c  <div                          s)): ?>
centMessagey($ref (empt     <?php i            ">
       "card-bodyv class=       <di  
           </div>                  /i></a>
  "><arrow-right="bi bi-ss cla<iw All ie-all">Viewass="vhp" cl"messages.p href=        <a           
     essages</h3>Recent Mi> </ory">ock-hist bi-clclass="bih3><i         <                -header">
rdlass="ca<div c                  
  ade-up">ta-aos="f-card" datyctivi="a <div class               ty-row">
viss="acti   <div cla
         Activity -->nt ece-- R          <!    
  >
          </div      >
    </div       
         div>   </             
    "></canvas>artnChibutio"distrcanvas id=          <             d-body">
 arass="c<div cl                 div>
        </           iv>
         </d                 
  ></button>e-dots"></i"bi bi-thre class=con"><iss="btn-i<button cla                     
       -actions">lass="cardv c<di              
          h3>ution</Distribtent ></i> Conpie-chart"i bi-"b><i class=     <h3           
        ader">"card-heiv class=    <d           
     y="200">os-delaa-aade-up" dat"fa-aos=t-card" datss="chariv cla        <d             
          div>
     </            >
     </div               canvas>
tChart"></tenons id="c      <canva        
          dy">s="card-bo  <div clas               >
     </div       
                </div>           
        </button>dots"></i>hree--tbiass="bi cln"><i ss="btn-icotton cla  <bu                     ">
     ns"card-actio <div class=                      ew</h3>
 Overvi/i> Content "><hartbi-bar-ci ass="b cl     <h3><i                  ader">
 ="card-heass<div cl                 
   ade-up">ta-aos="frt-card" dalass="cha     <div c         w">
  arts-ro="ch <div class
            Row -->- Charts       <!-  
     
          div>         </v>
           </di>
        /div   <                
       </div>             ive
     "></i> Acteld-checki-shilass="bi b       <i c                    
 e">stat-chang class="  <div                 iv>
     /drs<Admin Use">abelt-ls="sta   <div clas                 </div>
    talUsers; ?>cho $tor"><?php enumbet-tass="s    <div cla                
    tails">t-de"stadiv class=         <          v>
     </di              l"></i>
  ple-fil"bi bi-peolass=i c       <      
           ers">tat-icon usclass="s<div             
        0">y="50-delata-aos da"s="fade-upao" data-stat-cardiv class="     <d               
           </div>
                 </div>
                v>
    di  </                   all
    View ></i> bi-dash"="bi    <i class                     e">
   "stat-chang class=iv   <d                   
  v>ges</dissat-label">Me class="sta   <div                   ?></div>
  ]; s'tact_messageats['conp echo $st<?phnumber">"stat-<div class=                      s">
  at-detailss="st <div cla                   </div>
                    
i>"></filli-envelope-="bi bclass       <i                 
 ges"> messastat-iconclass="v       <di           >
   elay="400"" data-aos-d"fade-upaos=a--card" dat"stat<div class=     
                     >
         </div        
     iv>    </d                  </div>
                   
    this monthi> 15%rrow-up"></ss="bi bi-a   <i cla                        ive">
 hange positass="stat-ccl <div                        
les</div>bel">Artictat-la"sdiv class=         <                ?></div>
rticles']; $stats['a"><?php echomberss="stat-nu<div cla                      ">
  at-detailsss="st   <div cla              div>
         </            i>
  wspaper"></-nes="bi bi  <i clas                      icles">
con arttat-iv class="s         <di          "300">
 a-aos-delay=" dat"fade-upta-aos=ard" da="stat-c  <div class            
              iv>
             </d     
  /div>         <        iv>
    </d                  
     h montthis</i> 5% rrow-up">="bi bi-aclass  <i                        ">
   sitive poat-changes="stiv clas          <d           
   div>bs</">Proverbels="stat-la<div clas                      iv>
  s']; ?></d'proverbs[echo $stat"><?php "stat-numberlass=  <div c            
          ls">t-detai"staass=cl  <div          
             </div>          
      ill"></i>t-quote-f bi-chass="bi cla   <i                 
    ">verbsprocon t-i="stassla c       <div            200">
 -delay=" data-aos-up"os="fade data-aat-card" class="st<div                    
 
           v> </di             iv>
  </d                    
div>    </                onth
     this m> 8%row-up"></i"bi bi-ari class=        <                    ">
 positivechange"stat-<div class=                       
 ds</div>nary Worio>Dictel""stat-lablass= civ <d                    iv>
   ; ?></dnary']dictiotats['echo $sr"><?php beum"stat-nclass=       <div               >
   tails"stat-de class="       <div           </div>
                   </i>
   xt">journal-tei bi-lass="b    <i c                 ">
   ctionaryn diat-icoclass="st    <div            
     >0"="10delaydata-aos-" e-up"faddata-aos=-card" "statss=<div cla                  
                  </div>
            iv>
    </d               div>
    </                    his month
 > 12% tup"></irrow-"bi bi-aass=       <i cl                  
   sitive">t-change poass="sta <div cl                  
     ns</div>sootal Lest-label">Tta class="s     <div                 ?></div>
  sons']; tats['les $shor"><?php ecat-numbe class="st <div                  >
     "etailsat-dstv class="      <di                </div>
           >
       fill"></iook-"bi bi-b class=    <i                  ns">
  t-icon lesso"stadiv class=   <                 >
="fade-up"ata-aosat-card" dv class="stdi   <             d">
"stats-griiv class=      <d>
      --Stats Grid   <!-- 
            
          div>        </    >
      </div  n>
        /butto       <      ord
       "></i> Add Wirclebi-plus-cs="bi   <i clas               ">
       d'ction=ad.php?ationaryn.href='dicocationclick="ly" oondartn-sec"btn bton class=       <but            utton>
     </b             son
    Lese"></i> Addrclplus-cii-ss="bi b     <i cla            
       =add'">hp?actionf='lessons.pation.hre"lock=clicary" ontn btn-prim"btton class=bu <                  s">
 tionac"welcome-=lass    <div c            v>
    </di          day.</p>
   Hub toaraunyakitwith your Rning ppeat's ha<p>Here's wh             
       2>?>! 👋</h); name']ON['userSESSIlchars($_lspeciaecho htm, <?php me backh2>Welco  <                  ontent">
"welcome-class=v cdi       <    ">
     section"welcome-class=       <div      >
 Section --lcome <!-- We                   
ader>
    </he            
      </div>        a>
      </           
      View Site                      ></i>
 "eyes="bi bi-i clas         <               te">
view-sin-s="btl" clasx.htmf="../inde   <a hre              v>
           </di            "></i>
i bi-gears="bclas        <i          >
       item"s="header-asdiv cl   <             </div>
                >
        </spanbadge">3otification-ss="nn cla  <spa                     "></i>
 -bells="bi bi<i clas                      -item">
  headerlass="v c       <di         
    t">"header-righclass=div         <           </div>
    >
         hboard</h1>Das         <h1           >
   </button           
      "></i>isti-lbi b"<i class=                
        arToggle">" id="sidebeggl-tobilelass="mo   <button c      
           ">-leftaderss="heiv cla     <d         der">
  "admin-heaclass=ader        <he
     Bar -->   <!-- Top    >
      ntent""admin-co= classain      <m-->
  ntent in Co-- Ma
        <!        side>
    </aiv>
          </d    </div>
                  iv>
     </d           iv>
    le']); ?></dESSION['roucfirst($_Sphp echo er-role"><?"usv class=    <di          
          div> ?></ame']);['usernSSIONrs($_SEalchamlspeciecho ht><?php ser-name"ass="u<div cl              
          tails">r-de"useclass=v          <di       iv>
            </d         ></i>
   ircle" bi-person-c="biclass         <i             atar">
   r-av="useiv class     <d        ">
       er-profileuss=" clas      <div
          r-footer">bas="sideclasiv   <d     
             v>
    na</      >
      </a       >
         ut</span>Logo    <span                ></i>
-right"ox-arrow"bi bi-b <i class=              >
     "tem logoutass="nav-i.php" cl"logout<a href=                   </a>
           if; ?>
  php end          <?      n>
    pa ?></ses'];act_messagcontho $stats['><?php ecge"="badsspan cla          <s          