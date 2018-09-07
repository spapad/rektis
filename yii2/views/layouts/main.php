<?php
/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
        <title>
            <?= Html::encode($this->title) ?>
        </title>
        <link rel="icon" href="favicon-32x32.png" sizes="32x32" />
        <link rel="icon" href="favicon-192x192.png" sizes="192x192" />
        <?php $this->head() ?>
</head>

<body>
    <?php $this->beginBody() ?>

    <div class="wrap">
        <?php
        NavBar::begin([
            'brandLabel' => Yii::$app->params['companyName'],
            'brandUrl' => Yii::$app->homeUrl,
            'options' => [
                'class' => 'navbar-inverse navbar-fixed-top',
            ],
        ]);
        echo Nav::widget([
            'activateParents' => true,
            'options' => ['class' => 'navbar-nav navbar-right'],
            'items' => [
                [
                    'label' => 'Διαχείριση',
                    'visible' => Yii::$app->user->can('admin'),
                    'items' => [
                        '<li class="dropdown-header"><i class="glyphicon glyphicon-user"></i> Χρήστες</li>',
                        [
                            'label' => 'Όλοι οι χρήστες',
                            'url' => ['/user/index']
                        ],
                        [
                            'label' => 'Νέος χρήστης',
                            'url' => ['/user/create']
                        ],

                        '<li class="divider"></li>',
                        '<li class="dropdown-header"><i class="glyphicon glyphicon-bullhorn"></i> ' . Yii::t('app', 'Announcements') . '</li>',
                        [
                            'label' => Yii::t('app', 'All announcements'),
                            'url' => ['/announcement/index']
                        ],
                        [
                            'label' => Yii::t('app', 'Create Announcement'),
                            'url' => ['/announcement/create']
                        ],

                        '<li class="divider"></li>',
                        '<li class="dropdown-header"><i class="glyphicon glyphicon-pencil"></i> Σελίδες</li>',
                        [
                            'label' => 'Όλες οι σελίδες',
                            'url' => ['/Pages/page/index']
                        ],
                        [
                            'label' => 'Νέα σελίδα',
                            'url' => ['/Pages/page/create']
                        ],

                        '<li class="divider"></li>',
                        '<li class="dropdown-header"><i class="glyphicon glyphicon-dashboard"></i> Παράμετροι</li>',
                        [
                            'label' => 'Auth items',
                            'url' => ['/auth-item'],
                            'visible' => Yii::$app->user->can('admin'),
                        ],
                        [
                            'label' => 'Auth item connections',
                            'url' => ['/auth-item-child'],
                            'visible' => Yii::$app->user->can('admin'),
                        ],
                        [
                            'label' => 'Auth assignments',
                            'url' => ['/auth-assignment'],
                            'visible' => Yii::$app->user->can('admin'),
                        ],
                        [
                            'label' => 'Auth rules',
                            'url' => ['/auth-rule'],
                            'visible' => Yii::$app->user->can('admin'),
                        ],
                    ]
                ],

                [
                    'label' => 'Παράμετροι',
                    'visible' => !Yii::$app->user->isGuest,
                    'items' => [
                        '<li class="dropdown-header"><i class="glyphicon glyphicon-cog"></i> Εφαρμογής</li>',
                        [
                            'label' => 'Ειδικότητες',
                            'url' => ['/specialisation/index'],
                            'visible' => Yii::$app->user->can('user')
                        ],
                        [
                            'label' => 'Υπηρεσίες',
                            'url' => ['/service'],
                            'visible' => Yii::$app->user->can('user')
                        ],
                        [
                            'label' => 'Θέσεις',
                            'url' => ['/position'],
                            'visible' => Yii::$app->user->can('user')
                        ],
                        [
                            'label' => 'Καταστάσεις υπαλλήλων',
                            'url' => ['/employee-status'],
                            'visible' => Yii::$app->user->can('user')
                        ],

                        '<li class="divider"></li>',
                        '<li class="dropdown-header"><i class="glyphicon glyphicon-sunglasses"></i> Αδειών</li>',
                        [
                            'label' => 'Είδη αδειών',
                            'url' => ['/leave-type'],
                            'visible' => Yii::$app->user->can('leave_user')
                        ],

                        '<li class="divider"></li>',
                        '<li class="dropdown-header"><i class="glyphicon glyphicon-plane"></i> Μετακινήσεων</li>',
                        [
                            'label' => 'Αποστάσεις',
                            'url' => ['/transport-distance'],
                            'visible' => Yii::$app->user->can('transport_user')
                        ],
                        [
                            'label' => 'Είδη μετακινήσεων',
                            'url' => ['/transport-type'],
                            'visible' => Yii::$app->user->can('transport_user')
                        ],
                        [
                            'label' => 'Μέσα μετακίνησης',
                            'url' => ['/transport-mode'],
                            'visible' => Yii::$app->user->can('transport_user')
                        ],
                        [
                            'label' => 'Αποφάσεις ανάληψης υποχρέωσης',
                            'url' => ['/transport-funds'],
                            'visible' => Yii::$app->user->can('transport_user')
                        ],
                        [
                            'label' => 'Καταστάσεις μετακινήσεων',
                            'url' => ['/transport-status'],
                            'visible' => Yii::$app->user->can('transport_user')
                        ],
                    ],
                ],

                [
                    'label' => 'Εργαζόμενοι',
                    'visible' => Yii::$app->user->can('user') || Yii::$app->user->can('leave_user') || Yii::$app->user->can('transport_user'),
                    'items' => [

                        [
                            'label' => 'Όλοι οι εργαζόμενοι',
                            'url' => ['/employee/index'],
                            'visible' => Yii::$app->user->can('user')
                        ],

                        '<li class="divider"></li>',
                        '<li class="dropdown-header"><i class="glyphicon glyphicon-sunglasses"></i></li>',
                        [
                            'label' => 'Άδειες',
                            'url' => ['/leave'],
                            'visible' => Yii::$app->user->can('leave_user')
                        ],
                        [
                            'label' => 'Αρχεία εκτύπωσης αδειών',
                            'url' => ['/leave-print'],
                            'visible' => Yii::$app->user->can('leave_user')
                        ],
                        [
                            'label' => 'Αποφάσεις μεταφοράς υπολοίπων αδειών',
                            'url' => ['/leave-balance'],
                            'visible' => Yii::$app->user->can('leave_user')
                        ],

                        '<li class="divider"></li>',
                        '<li class="dropdown-header"><i class="glyphicon glyphicon-plane"></i></li>',
                        [
                            'label' => 'Μετακινήσεις',
                            'url' => ['/transport'],
                            'visible' => Yii::$app->user->can('transport_user')
                        ],
                        [
                            'label' => 'Αρχεία εκτύπωσης μετακινήσεων',
                            'url' => ['/transport-print'],
                            'visible' => Yii::$app->user->can('transport_user')
                        ],
                        [
                            'label' => 'Υπόλοιπα Κ.Α.Ε. μετακινήσεων',
                            'url' => ['/transport/kae'],
                            'visible' => Yii::$app->user->can('transport_user')
                        ],
                    ],
                ],

                [
                    'label' => 'Αναπληρωτές',
                    'visible' => Yii::$app->user->can('spedu_user'),
                    'items' => [
                        '<li class="dropdown-header"><i class="glyphicon glyphicon-file"></i> Αρχεία δεδομένων</li>',
                        [
                            'label' => 'Διαθέσιμα αρχεία',
                            'url' => [ '/SubstituteTeacher/substitute-teacher-file/index' ]
                        ],
                        [
                            'label' => 'Μεταφόρτωση αρχείων',
                            'url' => [ '/SubstituteTeacher/substitute-teacher-file/upload' ]
                        ],

                        '<li class="divider"></li>',
                        '<li class="dropdown-header"><i class="glyphicon glyphicon-cog"></i> Παράμετροι</li>',
                        [
                            'label' => 'Πράξεις',
                            'url' => [ '/SubstituteTeacher/operation/index' ]
                        ],
                        [
                            'label' => 'Σχέσεις πράξεων - ειδικοτήτων',
                            'url' => [ '/SubstituteTeacher/operation-specialisation/index' ],
                            'visible' => Yii::$app->user->can('admin'),
                        ],
                        [
                            'label' => 'Περιφερειακές Ενότητες',
                            'url' => [ '/SubstituteTeacher/prefecture/index' ]
                        ],
                        [
                            'label' => 'Ειδικότητες',
                            'url' => [ '/specialisation/index' ]
                        ],
                        [
                            'label' => 'Μητρώο αναπληρωτών',
                            'url' => [ '/SubstituteTeacher/teacher-registry/index' ]
                        ],

                        '<li class="divider"></li>',
                        '<li class="dropdown-header"><i class="glyphicon glyphicon-list"></i> Στοιχεία</li>',
                        [
                            'label' => 'Λειτουργικά κενά',
                            'url' => [ '/SubstituteTeacher/position/index' ]
                        ],
                        [
                            'label' => 'Προσκλήσεις προσλήψεων',
                            'url' => [ '/SubstituteTeacher/call/index' ]
                        ],
                        [
                            'label' => 'Κατανομές ΠΥΣΕΕΠ',
                            'url' => [ '/SubstituteTeacher/call-position/index' ]
                        ],
                        [
                            'label' => 'Αναπληρωτές',
                            'url' => [ '/SubstituteTeacher/teacher/index' ]
                        ],
                        [
                            'label' => 'Προτιμήσεις τοποθέτησης αναπληρωτών',
                            'url' => [ '/SubstituteTeacher/placement-preference/index' ],
                            'visible' => Yii::$app->user->can('admin'),
                        ],
                        [
                            'label' => 'Πίνακες διορισμών',
                            'url' => [ '/SubstituteTeacher/teacher-board/index' ]
                        ],
                        [
                            'label' => 'Καταγραφή κατάστασης αναπληρωτών',
                            'url' => [ '/SubstituteTeacher/teacher-status-audit/index' ]
                        ],
                        [
                            'label' => 'Διαχείριση αιτήσεων',
                            'url' => [ '/SubstituteTeacher/application/index' ]
                        ],
                        [
                            'label' => 'Τοποθετήσεις',
                            'url' => [ '/SubstituteTeacher/placement/index' ]
                        ],
                        [
                            'label' => 'Παραχθέντα έγγραφα',
                            'url' => [ '/SubstituteTeacher/placement-print/index' ],
                            'visible' => Yii::$app->user->can('admin'),
                        ],

                        '<li class="divider"></li>',
                        '<li class="dropdown-header"><i class="glyphicon glyphicon-export"></i> Διαλειτουργικότητα</li>',
                        [
                            'label' => 'Κατάσταση υπηρεσιών',
                            'url' => [ '/SubstituteTeacher/bridge/remote-status' ]
                        ],
                        [
                            'label' => 'Τροφοδότηση συστήματος αιτήσεων',
                            'url' => [ '/SubstituteTeacher/bridge/send' ]
                        ],
                        [
                            'label' => 'Λήψη από το σύστημα αιτήσεων',
                            'url' => [ '/SubstituteTeacher/bridge/receive' ]
                        ],

                        '<li class="divider"></li>',
                        '<li class="dropdown-header"><i class="glyphicon glyphicon-list-alt"></i> Αρχείο καταγραφής</li>',
                        [
                            'label' => 'Στοιχεία καταγραφής (audit log)',
                            'url' => [ '/SubstituteTeacher/audit-log/index' ]
                        ],
                    ],
                ],

                [
                    'label' => 'Διαχείριση Δαπανών',
                    'visible' => Yii::$app->user->can('financial_director') || Yii::$app->user->can('financial_editor') || Yii::$app->user->can('financial_viewer'),
                    'items' => [
                        '<li class="dropdown-header"><i class="glyphicon glyphicon-euro"></i> Ενότητες</li>',
                        [
                            'label' => 'Κεντρική Διαχείριση',
                            'url' => ['/finance/default']
                        ],
                        [
                            'label' => 'Διαχείριση Οικονομικού Έτους',
                            'url' => ['/finance/default/administeryear'],
                        ],
                        [
                            'label' => 'Παράμετροι',
                            'url' => ['/finance/default/parameterize'],
                        ],
                        '<li class="divider"></li>',
                        '<li class="dropdown-header"><i class="glyphicon glyphicon-share-alt"></i> Συντομεύσεις</li>',
                        [
                            'label' => 'Δαπάνες',
                            'url' => ['/finance/finance-expenditure'],
                        ],
                        [
                            'label' => 'Τιμολόγια',
                            'url' => ['/finance/finance-invoice'],
                        ],
                        [
                            'label' => 'Προμηθευτές',
                            'url' => ['/finance/finance-supplier']
                        ]
                    ],
                ],
                [
                    'label' => 'Σχολικές Μετακινήσεις',
                    'visible' => Yii::$app->user->can('schtransport_director') || Yii::$app->user->can('schtransport_editor') || Yii::$app->user->can('schtransport_viewer'),
                    'items' => [
                        '<li class="dropdown-header"><i class="glyphicon glyphicon-plane"></i></li>',
                        [
                            'label' => 'Εγκρίσεις Μετακινήσεων',
                            'url' => ['/schooltransport/schtransport-transport'],
                        ],
                        [
                            'label' => 'Αρχειοθετημένες Εγκρίσεις Μετακινήσεων',
                            'url' => ['/schooltransport/schtransport-transport/index?archived=1'],
                        ],
                        [
                            'label' => 'Προγράμματα Μετακινήσεων',
                            'url' => ['/schooltransport/schtransport-program'],
                        ],
                        [
                            'label' => 'Στατιστικά',
                            'url' => ['/schooltransport/statistic/index'],
                        ],
                        '<li class="divider"></li>',
                        '<li class="dropdown-header"><i class="glyphicon glyphicon-cog"></i> Παράμετροι</li>',
                        [
                            'label' => 'Σχολικές Μονάδες',
                            'url' => ['/schooltransport/schoolunit']
                        ],
                        [
                            'label' => 'Καταστάσεις Εγκρίσεων',
                            'url' => ['/schooltransport/schtransport-state']
                        ],
                        '<li class="divider"></li>',
                        '<li class="dropdown-header"><i class="glyphicon glyphicon-question-sign"></i> Βοήθεια</li>',
                        [
                            'label' => 'Βοήθεια εφαρμογής σχολικών μετακινήσεων',
                            'url' => ['/schooltransport/default/help?helpId=1#schtransportsapp_help']
                        ],
                        [
                            'label' => 'Διαδικασία έγκρισης σχολικής μετακίνησης',
                            'url' => ['/schooltransport/default/help?helpId=2#schtransports_help']
                        ],
                        [
                            'label' => 'Νομοθεσία σχολικών μετακινήσεων',
                            'url' => ['/schooltransport/default/help?helpId=3#legislation']
                        ],
                    ],
                ],
                Yii::$app->user->isGuest ? [
                    'label' => 'Σχετικά',
                    'url' => ['/site/about']
                ] : '',
                Yii::$app->user->isGuest ? [
                    'label' => 'Επικοινωνία',
                    'url' => ['/site/contact']
                ] : '',
                Yii::$app->user->isGuest ?
                        [
                            'label' => '<i class="glyphicon glyphicon-log-in"></i> Είσοδος',
                            'encode' => false,
                            'visible' => Yii::$app->user->isGuest,
                            'url' => ['/site/login']
                        ] :
                        [
                            'label' => '<i class="glyphicon glyphicon-user"></i>',
                            'encode' => false,
                            'visible' => !Yii::$app->user->isGuest,
                            'items' => [
                                '<li class="dropdown-header">' . Yii::$app->user->identity->username . '</li>',
                                [
                                    'label' => 'Ο λογαριασμός μου',
                                    'url' => ['/user/account']
                                ],
                                '<li class="divider"></li>',
                                [
                                    'label' => 'Σχετικά',
                                    'url' => ['/site/about']
                                ],
                                [
                                    'label' => 'Επικοινωνία',
                                    'url' => ['/site/contact']
                                ],
                                '<li class="divider"></li>',
                                [
                                    'label' => '<i class="glyphicon glyphicon-log-out"></i> Έξοδος',
                                    'encode' => false,
                                    'url' => ['/site/logout'],
                                    'linkOptions' => ['data-method' => 'post']
                                ],
                            ],
                        ],
            ],
        ]);
        NavBar::end();
        ?>

            <div class="container">
                <?=
            Breadcrumbs::widget([
                'homeLink' => [ 'label' => 'Αρχική', 'url' => ['/site/index']],
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ])
            ?>
                    <?php
            foreach (Yii::$app->session->getAllFlashes() as $key => $message) {
                if (!is_array($message)) {
                    $messages = [$message];
                } else {
                    $messages = $message;
                }
                echo '<div class="alert alert-' . $key . '">' . implode('<br/>', $messages) . '</div>';
            }
            ?>
                        <?= $content ?>
            </div>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="container">
                <p class="pull-left">&copy;
                    <?= Yii::$app->params['companyName'] ?>
                        <?= date('Y') ?> |
                            <?= Html::a('Αρχική', ['site/index']) ?> |
                                <?= Html::a('Σχετικά', ['site/about']) ?> |
                                    <?= Html::a('Επικοινωνία', ['site/contact']) ?> |
                                        <?=
                    Yii::$app->user->isGuest ?
                            Html::a('Είσοδος', ['site/login']) :
                            Html::a('Έξοδος ' . Yii::$app->user->identity->username, ['site/logout'], ['data-method' => 'post'])
                    ?>
                </p>
                <p class="pull-right">
                    <?= Yii::powered() ?>
                </p>
            </div>
        </div>
    </footer>
    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>
