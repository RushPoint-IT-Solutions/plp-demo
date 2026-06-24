/* tor.js — logic for TOR form page */

var torCurrentRowId = null;
var torCurrentData = null;
var torCurrentPage = 1;
var torRestorePreviewClass = false;
var torRestorePreviewModal = false;

function torEscHtml(value) {
    return String(value || '').replace(/[&<>"']/g, function(ch) {
        var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
        return map[ch];
    });
}

function torGetRowData(rowId) {
    var row = torGetRow(rowId);
    if (!row) return null;

    var cells = row.querySelectorAll('td');
    return {
        studentNo: (cells[1] ? cells[1].textContent : '').trim(),
        studentName: (cells[2] ? cells[2].textContent : '').trim(),
        program: (cells[3] ? cells[3].textContent : '').trim(),
        year: (cells[4] ? cells[4].textContent : '').trim(),
        section: (cells[5] ? cells[5].textContent : '').trim()
    };
}

function torProgramFull(program) {
    var key = String(program || '').trim().toUpperCase();
    var map = {
        'BSCS': 'BACHELOR OF SCIENCE IN COMPUTER SCIENCE',
        'BSIT': 'BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY',
        'BSED': 'BACHELOR OF SECONDARY EDUCATION',
        'BSBA': 'BACHELOR OF SCIENCE IN BUSINESS ADMINISTRATION',
        'BS ENTREPRENEURSHIP': 'BACHELOR OF SCIENCE IN ENTREPRENEURSHIP',
        'BSENT': 'BACHELOR OF SCIENCE IN ENTREPRENEURSHIP'
    };

    return map[key] || key || 'BACHELOR PROGRAM';
}

function torBuildPages(data) {
    var pages = [];

    // --- Page 1 ---
    var issueDate = new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    var printedDateTime = new Date().toLocaleString('en-US', { month: 'numeric', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
    var p1 = '<div class="tor-page-content" style="font-size:0.92rem; line-height:1.4; color:#000; font-family:\'Times New Roman\', Times, serif;">' +
        '<div class="tor-header-space" style="height: 3in;"></div>' +

        '<div style="border-top:1px solid #111; border-bottom:1px solid #111; font-weight:700; text-align:center; letter-spacing:0.05em; font-size:0.98rem; padding:4px 6px; margin-bottom:10px;">STUDENT DATA</div>' +

        '<div style="display:grid; grid-template-columns:1fr 220px; gap:12px; margin-bottom:10px;">' +
            '<table style="width:100%; border-collapse:collapse;">' +
            '<tr><td style="width:148px; padding:1px 0;">Student Number</td><td style="padding:1px 0;">: ' + torEscHtml(data.studentNo) + '</td></tr>' +
                '<tr><td style="padding:1px 0;">Name</td><td style="padding:1px 0;">: ' + torEscHtml(data.studentName).toUpperCase() + '</td></tr>' +
                '<tr><td style="padding:1px 0;">Address</td><td style="padding:1px 0;">: 123 SAMPLE STREET, BARANGAY SAMPLE, PASIG CITY</td></tr>' +
                '<tr><td style="padding:1px 0;">Sex</td><td style="padding:1px 0;">: FEMALE</td></tr>' +
                '<tr><td style="padding:1px 0;">Date of Birth</td><td style="padding:1px 0;">: APRIL 1, 1993</td></tr>' +
                '<tr><td style="padding:1px 0;">Place of Birth</td><td style="padding:1px 0;">: SORSOGON</td></tr>' +
                '<tr><td style="padding:1px 0;">Date of Admission</td><td style="padding:1px 0;">: 2011</td></tr>' +
                '<tr><td style="padding:1px 0;">Admission Credential</td><td style="padding:1px 0;">: FIRST-YEAR</td></tr>' +
                '<tr><td style="padding:1px 0; vertical-align:top;">Program</td><td style="padding:1px 0;">: ' + torEscHtml(torProgramFull(data.program)) + '</td></tr>' +
                '<tr><td style="padding:1px 0;">Date of Completion</td><td style="padding:1px 0;">: MARCH 05, 2015</td></tr>' +
                '<tr><td style="padding:1px 0;">Date of Graduation</td><td style="padding:1px 0;">: MARCH 27, 2015</td></tr>' +
                '<tr><td style="padding:1px 0;">Resolution No.</td><td style="padding:1px 0;">: 01-057, SERIES OF 2015</td></tr>' +
            '</table>' +
            '<div style="display:flex; flex-direction:column; align-items:flex-start; margin-left:0;">' +
                '<div style="border:1px solid #111; height:210px; width:190px; margin:0 0 6px;"></div>' +
                '<div style="width:190px; text-align:center; font-size:0.7rem; font-style:italic;">Not Valid Without University Seal and Signature</div>' +
            '</div>' +
        '</div>' +

        '<div style="border-top:1px solid #111; border-bottom:1px solid #111; font-weight:700; text-align:center; letter-spacing:0.05em; font-size:0.98rem; padding:4px 6px; margin:10px 0 8px;">SCHOLASTIC RECORD</div>' +
        '<table style="width:100%; border-collapse:collapse; margin-bottom:8px;">' +
            '<tr><td style="width:130px; padding:1px 0;">Elementary</td><td style="padding:1px 0;">: VISINITAHAAN ELEMENTARY SCHOOL</td></tr>' +
            '<tr><td style="padding:1px 0;">Year Graduated</td><td style="padding:1px 0;">: 2006</td></tr>' +
            '<tr><td style="padding:1px 0;">High School</td><td style="padding:1px 0;">: DONSOL NATIONAL COMPREHENSIVE HIGH SCHOOL</td></tr>' +
            '<tr><td style="padding:1px 0;">Year Graduated</td><td style="padding:1px 0;">: 2010</td></tr>' +
        '</table>' +

        '<table style="width:100%; border-collapse:collapse; margin-bottom:8px; border-top:1px solid #111; border-bottom:1px solid #111;">' +
            '<tr style="border-top:1px solid #111; border-bottom:1px solid #111;">' +
                '<th style="width:67%; font-weight:700; text-align:center; letter-spacing:0.04em; font-size:0.96rem; padding:4px 0; border-right:1px solid #111;">GRADING SYSTEM</th>' +
                '<th style="width:33%; font-weight:700; text-align:center; letter-spacing:0.04em; font-size:0.96rem; padding:4px 0;">REMARKS</th>' +
            '</tr>' +
            '<tr>' +
                '<td style="vertical-align:top; padding:3px 6px; border-right:1px solid #111;">' +
                    '<table style="width:100%; border-collapse:collapse; font-size:0.74rem; line-height:1.1;">' +
                        '<tr><td style="width:24%;">1.00 = 97.5-100</td><td style="width:24%;">2.25 = 82.5-85.4</td><td style="width:10%;">INC</td><td style="width:42%;">Incomplete</td></tr>' +
                        '<tr><td>1.25 = 94.5-97.4</td><td>2.50 = 79.5-82.4</td><td>OD</td><td>Officially Dropped</td></tr>' +
                        '<tr><td>1.50 = 91.5-94.4</td><td>2.75 = 76.5-79.4</td><td>UD</td><td>Unofficially Dropped</td></tr>' +
                        '<tr><td>1.75 = 88.5-91.4</td><td>3.00 = 74.5-76.4</td><td>NC</td><td>No Credit</td></tr>' +
                        '<tr><td>2.00 = 85.5-88.4</td><td>5.00 = 74.4 & below</td><td>NGA</td><td>Grade Not Available</td></tr>' +
                    '</table>' +
                    '<div style="font-size:0.73rem; line-height:1.16; margin-top:5px;">' +
                        'Credit/s:<br>' +
                        'One unit of credit is one hour lecture or recitation or three hours of laboratory work each week except courses in Filipino and other foreign languages.For the period of a complete semester. The medium of instruction in this University is English' +
                    '</div>' +
                '</td>' +
                '<td style="vertical-align:top; text-align:center; padding:8px 8px 0; font-size:0.86rem; line-height:1.24;">FOR EMPLOYMENT PURPOSES ONLY</td>' +
            '</tr>' +
        '</table>' +

        '<div style="font-size:0.82rem; line-height:1.34; margin-bottom:8px; color:#000;">' +
            'This copy is an exact reproduction of the original transcript on file with the Office of the University Registrar and should be considered as original copy when signed by the certifying registrar and impressed with the university seal. Any erasure or alteration of this transcript renders the whole document invalid unless authenticated by the signature of the foregoing official.' +
        '</div>' +

        '<div style="border-top:1px solid #111; border-bottom:1px solid #111; margin-top:8px;">' +
            '<div style="display:grid; grid-template-columns:1fr 1fr;">' +
                '<div style="padding:6px 10px 8px;">' +
                    '<div style="font-weight:700; margin-bottom:18px;">Prepared by :</div>' +
                    '<div style="height:22px;"></div>' +
                    '<div style="text-align:center; font-weight:700;">MS. JULIE RUTH C. MALABANAN</div>' +
                    '<div style="text-align:center; font-style:italic;">College Secretary</div>' +
                '</div>' +
                '<div style="border-left:1px solid #111; padding:6px 10px 8px;">' +
                    '<div style="font-weight:700; margin-bottom:18px;">Checked by :</div>' +
                    '<div style="height:22px;"></div>' +
                    '<div style="text-align:center; font-weight:700;">MS. JAY ANNE I. SANTOS</div>' +
                    '<div style="text-align:center; font-style:italic;">Assistant Registrar</div>' +
                '</div>' +
            '</div>' +
            '<div style="border-top:1px solid #111; text-align:center; padding:6px 8px 8px;">' +
                '<div style="font-weight:700; margin-bottom:16px;">Certified True and Correct:</div>' +
                '<div style="height:20px;"></div>' +
                '<div style="display:inline-block; min-width:260px; font-weight:700;">FEDERICO G. NUEVA, MT, MT</div>' +
                '<div style="font-style:italic;">University Registrar</div>' +
            '</div>' +
        '</div>' +

        '<div style="display:flex; justify-content:flex-start; align-items:flex-end; margin-top:10px;">' +
            '<div style="font-size:0.68rem; text-align:left; min-width:220px; margin-left:1in;">' +
                '<div style="font-style:italic;">Not Valid Without University Seal</div>' +
                '<div>Date issued: <span style="display:inline-block; min-width:88px; border-bottom:1px solid #111;">' + torEscHtml(issueDate) + '</span></div>' +
                '<div>Page 1 of 4</div>' +
            '</div>' +
        '</div>' +
        '</div>';
    pages.push(p1);

    function torBuildLedgerRows(groups, tailText, minRows) {
        var rowsHtml = '';
        var spacerHeight = 8;
        var termFont = '0.82rem';
        var termPad = '2px 5px';
        var rowPad = '1px 4px';
        var rowPadDesc = '1px 5px';
        var rowFont = '0.78rem';
        var rowFontMid = '0.76rem';
        var rowLine = '1.08';
        var tailFont = '0.78rem';
        var renderedRows = 0;

        groups.forEach(function(group, groupIndex) {
            // One blank row before first semester; two rows before each next semester.
            var blankRows = groupIndex === 0 ? 1 : 2;
            for (var blank = 0; blank < blankRows; blank++) {
                rowsHtml += '<tr>' +
                    '<td style="height:' + spacerHeight + 'px; border-left:1px solid #111;"></td>' +
                    '<td style="border-right:1px solid #111;"></td>' +
                    '<td style="border-right:1px solid #111;"></td>' +
                    '<td style="border-right:1px solid #111;"></td>' +
                    '<td style="border-right:1px solid #111;"></td>' +
                    '<td style="border-right:1px solid #111;"></td>' +
                '</tr>';
                renderedRows++;
            }

            rowsHtml += '<tr>' +
                '<td style="width:8%; border-left:1px solid #111;"></td>' +
                '<td style="width:8%; border-right:1px solid #111;"></td>' +
                '<td style="width:54%; border-right:1px solid #111; padding:' + termPad + '; font-weight:700; font-size:' + termFont + ';">' + torEscHtml(group.term) + '</td>' +
                '<td style="width:10%; border-right:1px solid #111;"></td>' +
                '<td style="width:11%; border-right:1px solid #111;"></td>' +
                '<td style="width:9%; border-right:1px solid #111;"></td>' +
            '</tr>';
            renderedRows++;

            group.rows.forEach(function(row) {
                rowsHtml += '<tr>' +
                    '<td style="width:8%; border-left:1px solid #111; padding:' + rowPad + '; font-size:' + rowFont + '; line-height:' + rowLine + ';">' + torEscHtml(row[0]) + '</td>' +
                    '<td style="width:8%; border-right:1px solid #111; padding:' + rowPad + '; font-size:' + rowFont + '; line-height:' + rowLine + ';">' + torEscHtml(row[1]) + '</td>' +
                    '<td style="width:54%; border-right:1px solid #111; padding:' + rowPadDesc + '; font-size:' + rowFont + '; line-height:' + rowLine + ';">' + torEscHtml(row[2]) + '</td>' +
                    '<td style="width:10%; border-right:1px solid #111; text-align:center; padding:' + rowPad + '; font-size:' + rowFont + '; line-height:' + rowLine + ';">' + torEscHtml(row[3]) + '</td>' +
                    '<td style="width:11%; border-right:1px solid #111; text-align:center; padding:' + rowPad + '; font-size:' + rowFontMid + '; line-height:' + rowLine + ';">' + torEscHtml(row[4]) + '</td>' +
                    '<td style="width:9%; border-right:1px solid #111; text-align:center; padding:' + rowPad + '; font-size:' + rowFont + '; line-height:' + rowLine + ';">' + torEscHtml(row[5]) + '</td>' +
                '</tr>';
                renderedRows++;
            });
        });

        if (tailText) {
            rowsHtml += '<tr>' +
                '<td style="border-left:1px solid #111;"></td>' +
                '<td style="border-right:1px solid #111;"></td>' +
                '<td style="border-right:1px solid #111; padding:' + termPad + '; font-size:' + tailFont + '; font-weight:700;"><span style="display:inline-block; padding-left:26px;">' + torEscHtml(tailText) + '</span></td>' +
                '<td style="border-right:1px solid #111;"></td>' +
                '<td style="border-right:1px solid #111;"></td>' +
                '<td style="border-right:1px solid #111;"></td>' +
            '</tr>';
            renderedRows++;

            rowsHtml += '<tr>' +
                '<td style="height:' + spacerHeight + 'px; border-left:1px solid #111;"></td>' +
                '<td style="border-right:1px solid #111;"></td>' +
                '<td style="border-right:1px solid #111;"></td>' +
                '<td style="border-right:1px solid #111;"></td>' +
                '<td style="border-right:1px solid #111;"></td>' +
                '<td style="border-right:1px solid #111;"></td>' +
            '</tr>';
            renderedRows++;

            rowsHtml += '<tr>' +
                '<td style="height:' + spacerHeight + 'px; border-left:1px solid #111;"></td>' +
                '<td style="border-right:1px solid #111;"></td>' +
                '<td style="border-right:1px solid #111;"></td>' +
                '<td style="border-right:1px solid #111;"></td>' +
                '<td style="border-right:1px solid #111;"></td>' +
                '<td style="border-right:1px solid #111;"></td>' +
            '</tr>';
            renderedRows++;
        }

        if (minRows && renderedRows < minRows) {
            for (var filler = renderedRows; filler < minRows; filler++) {
                rowsHtml += '<tr>' +
                    '<td style="height:' + spacerHeight + 'px; border-left:1px solid #111;"></td>' +
                    '<td style="border-right:1px solid #111;"></td>' +
                    '<td style="border-right:1px solid #111;"></td>' +
                    '<td style="border-right:1px solid #111;"></td>' +
                    '<td style="border-right:1px solid #111;"></td>' +
                    '<td style="border-right:1px solid #111;"></td>' +
                '</tr>';
            }
        }

        return rowsHtml;
    }

    function torBuildLedgerPage(pageNo, groups, tailText, minRows) {
        var headFont = '0.8rem';
        var subHeadFont = '0.71rem';
        var topInfoFont = '0.92rem';
        var footerFont = '0.72rem';
        var qrSize = 58;

        var qrBlock = '<div style="position:relative; width:' + qrSize + 'px; height:' + qrSize + 'px; border:1px solid #111; background:repeating-conic-gradient(#111 0 25%, #fff 0 50%) 50% / 8px 8px;">' +
            '<div style="position:absolute; left:3px; top:3px; width:16px; height:16px; border:3px solid #111; background:#fff;"></div>' +
            '<div style="position:absolute; right:3px; top:3px; width:16px; height:16px; border:3px solid #111; background:#fff;"></div>' +
            '<div style="position:absolute; left:3px; bottom:3px; width:16px; height:16px; border:3px solid #111; background:#fff;"></div>' +
        '</div>';

        return '<div class="tor-page-content" style="font-family:\'Times New Roman\', Times, serif; color:#000;">' +
            '<div class="tor-header-space" style="height: 2in;"></div>' +
            '<div style="font-size:' + topInfoFont + '; margin-bottom:6px;">' +
                '<div style="display:grid; grid-template-columns:100px 1fr; align-items:end; column-gap:8px; margin-bottom:2px;">' +
                    '<div style="font-weight:700;">NAME</div>' +
                    '<div style="border-bottom:1px solid #111; padding:0 2px 1px; font-weight:700;">' + torEscHtml(data.studentName).toUpperCase() + '</div>' +
                '</div>' +
                '<div style="display:grid; grid-template-columns:100px 1fr; align-items:end; column-gap:8px;">' +
                    '<div style="font-weight:700;">STUDENT NO.</div>' +
                    '<div style="border-bottom:1px solid #111; padding:0 2px 1px; font-weight:700;">' + torEscHtml(data.studentNo) + '</div>' +
                '</div>' +
            '</div>' +
            '<table class="tor-ledger-table" style="width:100%; border-collapse:collapse; border-top:1px solid #111; border-bottom:1px solid #111; margin-bottom:0;">' +
                '<thead>' +
                    '<tr>' +
                        '<th colspan="2" rowspan="2" style="border:1px solid #111; border-right:1px solid #111; padding:2px 4px; text-align:center; font-size:' + headFont + ';">COURSE<br>NUMBER</th>' +
                        '<th rowspan="2" style="border:1px solid #111; padding:2px 4px; text-align:center; font-size:' + headFont + ';">DESCRIPTIVE TITLE OF THE COURSE</th>' +
                        '<th colspan="3" style="border:1px solid #111; padding:2px 4px; text-align:center; font-size:' + headFont + ';">GRADES</th>' +
                    '</tr>' +
                    '<tr>' +
                        '<th style="border:1px solid #111; border-top:none; padding:1px 2px; text-align:center; font-size:' + subHeadFont + ';">SEMESTRAL</th>' +
                        '<th style="border:1px solid #111; border-top:none; padding:1px 2px; text-align:center; font-size:' + subHeadFont + ';"><div style="line-height:1.0;">RE-EXAM</div><div style="line-height:1.0; border-top:1px solid #111; margin:1px -2px 0; padding-top:1px;">Completion</div></th>' +
                        '<th style="border:1px solid #111; border-top:none; padding:1px 2px; text-align:center; font-size:' + subHeadFont + ';">CREDITS</th>' +
                    '</tr>' +
                '</thead>' +
                '<tbody>' + torBuildLedgerRows(groups, tailText, minRows) + '</tbody>' +
            '</table>' +
            '<div style="display:flex; justify-content:space-between; align-items:flex-start; margin-top:4px;">' +
                '<div style="font-size:' + footerFont + '; line-height:1.22; margin-top:0;">' +
                    '<div>' + torEscHtml(printedDateTime) + '</div>' +
                    '<div>Prepared by: Malabanan, Juliette C.</div>' +
                    '<div>Checked by: Santos, Jay Angel</div>' +
                '</div>' +
                '<div style="display:flex; flex-direction:column; align-items:flex-end; margin-top:10px; margin-right:2px;">' +
                    qrBlock +
                '</div>' +
            '</div>' +
        '</div>';
    }

    var p2Groups = [
        {
            term: '2011-2012, FIRST SEMESTER',
            rows: [
                ['EN', '101', 'Study and Thinking Skills', '2.25', '', '3'],
                ['FIL', '101', 'Komunikasyon sa Akademikong Filipino', '2.25', '', '3'],
                ['EDMA', '100A', 'Basic Mathematics', '3.00', '', '3'],
                ['NS', '101N', 'Biological Science', '2.75', '', '3'],
                ['PCL', '1', 'Pasig Community Life', '2.50', '', '3'],
                ['SS', '101', 'Society and Culture with Family Planning', '2.50', '', '3'],
                ['DEVREAD', '101', 'Developmental Reading I', '1.75', '', '0']
            ]
        },
        {
            term: '2011-2012, SECOND SEMESTER',
            rows: [
                ['EN', '102', 'Writing in the Discipline', '2.50', '', '3'],
                ['FIL', '102', 'Pagbasa at Pagsulat tungo sa Pananaliksik', '2.50', '', '3'],
                ['EDMA', '100B', 'Contemporary Mathematics', '2.25', '', '3'],
                ['NS', '102N', 'Earth Science', '2.25', '', '3'],
                ['HI', '101', 'Philippine History', '2.25', '', '3'],
                ['PE', '102', 'Rhythmic Activities', '2.25', '', '2']
            ]
        },
        {
            term: '2012-2013, FIRST SEMESTER',
            rows: [
                ['FIL', '103', 'Masining na Pagpapahayag', '2.00', '', '3'],
                ['PAN', '103', 'Ang Panitikan ng Pilipinas', '1.50', '', '3'],
                ['EDMA', '105', 'Advanced Algebra and Trigonometry', '2.50', '', '3'],
                ['CHEM', '101N', 'Inorganic Chemistry', '2.50', '', '3'],
                ['LIT', '101', 'Philippine Literature', '2.25', '', '3'],
                ['VE', '101N', 'Personhood Development', '1.50', '', '3'],
                ['PSED', '101', 'Young Filipino and the Pre-School Education', '2.00', '', '3'],
                ['MUSIC', '101', 'Fundamentals of Music', '1.75', '', '3']
            ]
        },
        {
            term: '2012-2013, SECOND SEMESTER',
            rows: [
                ['PAN', '102', 'Pagpapahalaga sa mga Anyo ng Kontemporaryong Panitikang Filipino', '2.50', '', '3'],
                ['EDSC', '103A', 'Physics for Health Science', '2.75', '', '3'],
                ['SS', '102', 'General Psychology', '2.25', '', '3'],
                ['EDMA', '108', 'Plane and Solid Geometry', '2.50', '', '3'],
                ['EDUC', '109', 'Social Dimension of Education', '2.50', '', '3']
            ]
        },
        {
            term: '2012-2013, SUMMER',
            rows: [
                ['PSED', '102', 'Pre-School Curriculum and Technology', '2.25', '', '3'],
                ['FS', '1', 'Field Study 1', '1.50', '', '1'],
                ['PE', '104', 'Team Sports', '2.00', '', '2'],
                ['EDUC', '102', 'Facilitating Learning', '2.50', '', '3']
            ]
        }
    ];

    var p2 = torBuildLedgerPage(2, p2Groups, '-- plpxplpxplspxplx- Page 2 of 4 - plpxplpxplspxplx --', 60);
    pages.push(p2);

    var p3Groups = [
        {
            term: '2013-2014, SECOND SEMESTER',
            rows: [
                ['EDMA', '120', 'Problem Solving', '2.75', '', '3'],
                ['DEVREAD', '102', 'Developmental Reading II', '3.00', '', '3'],
                ['NS', '104', 'Astronomy', '3.00', '', '3'],
                ['SS', '103', 'Political Science with New Philippine Constitution', '2.50', '', '3'],
                ['LIT', '102', 'World Literature', '3.00', '', '3'],
                ['EDUC', '106B', 'Assessment of Student Learning II with Thesis Writing', '2.25', '', '3'],
                ['EDUC', '105B', 'Education Technology II', '2.00', '', '3'],
                ['FS', '4', 'Field Study 4', '1.75', '', '1'],
                ['FS', '5', 'Field Study 5', '1.75', '', '1'],
                ['FS', '6', 'Field Study 6', '2.25', '', '1']
            ]
        },
        {
            term: '2013-2014, SUMMER',
            rows: [
                ['PSED', '104', 'Language, Numeracy, Science and Health for Young Children', '1.75', '', '3'],
                ['PSED', '107', 'Guidance and Counseling in Pre-School Education including Special Education', '2.25', '', '3'],
                ['PSED', '110', 'Directed Pre School Study', '2.50', '', '3']
            ]
        },
        {
            term: '2014-2015, FIRST SEMESTER',
            rows: [
                ['EN', '104', 'Technical Writing', '2.00', '', '3'],
                ['FIL', '104', 'Retorika', '1.75', '', '3'],
                ['MATH', '201', 'Statistics and Probability', '2.25', '', '3'],
                ['SCI', '201', 'Earth and Life Science', '2.00', '', '3'],
                ['SOCSCI', '201', 'Introduction to Sociology', '2.50', '', '3'],
                ['PE', '201', 'Individual/Dual Games/Sports', '1.75', '', '2']
            ]
        },
        {
            term: '2014-2015, SECOND SEMESTER',
            rows: [
                ['EN', '105', 'Speech Communication', '2.00', '', '3'],
                ['FIL', '105', 'Pagsasalin sa Konteksto ng Filipino', '1.75', '', '3'],
                ['EDMA', '201', 'Mathematics of Investment', '2.25', '', '3'],
                ['NS', '201', 'Environmental Science', '2.00', '', '3'],
                ['EDUC', '201', 'Curriculum Development', '1.75', '', '3'],
                ['PSED', '201', 'Family and Community in Early Childhood', '2.00', '', '3']
            ]
        },
        {
            term: '2014-2015, SUMMER',
            rows: [
                ['PSED', '202', 'Teaching Strategies for Young Children', '1.75', '', '3'],
                ['PSED', '203', 'Assessment of Learning in ECCD', '2.00', '', '3']
            ]
        }
    ];

    var p3 = torBuildLedgerPage(3, p3Groups, '-- plpxplpxplspxplx- Page 3 of 4 - plpxplpxplspxplx --', 60);
    pages.push(p3);

    var p4Groups = [
        {
            term: '2014-2015, FIRST SEMESTER',
            rows: [
                ['PSED', '111', 'Trends and Issues in Early Childhood Education', '1.50', '', '3'],
                ['PSED', '112', 'Parent Education and Community Partnership', '1.75', '', '3'],
                ['PSED', '115', 'Classroom Management in ECCD', '1.75', '', '3'],
                ['PSED', '116', 'Learning Materials Development', '2.00', '', '3'],
                ['EDUC', '202', 'Educational Measurement', '2.25', '', '3'],
                ['EDUC', '203', 'Action Research in Education', '2.50', '', '3'],
                ['PRACT', '1', 'Practice Teaching', '1.25', '', '6'],
                ['RIZAL', '1', 'Life and Works of Rizal', '2.00', '', '3']
            ]
        },
        {
            term: '2014-2015, SECOND SEMESTER',
            rows: [
                ['PSED', '113', 'Early Childhood Program Development', '1.75', '', '3'],
                ['PSED', '114', 'Child Assessment and Evaluation', '1.50', '', '3'],
                ['PSED', '117', 'Inclusive Education in ECCD', '2.00', '', '3'],
                ['EDUC', '204', 'Seminar in Teaching Profession', '2.25', '', '3'],
                ['PRACT', '2', 'Internship in Pre-School Education', '1.25', '', '6'],
                ['THESIS', '1', 'Research Project', '1.75', '', '3'],
                ['THESIS', '2', 'Research Writing and Presentation', '1.50', '', '3']
            ]
        }
    ];

    var p4 = torBuildLedgerPage(4, p4Groups, '-- plpxplpxplspxplx- Page 4 of 4 - plpxplpxplspxplx --', 64);
    pages.push(p4);

    return pages;
}

function torRenderPreviewSheet(data) {
    var sheet = document.getElementById('torPreviewSheet');
    if (!sheet) return;

    var pages = torBuildPages(data);
    var totalPages = pages.length;
    if (torCurrentPage < 1) torCurrentPage = 1;
    if (torCurrentPage > totalPages) torCurrentPage = totalPages;

    sheet.innerHTML = '<div class="tor-sheet-page">' + pages[torCurrentPage - 1] + '</div>';

    var indicator = document.getElementById('torPageIndicator');
    var prevBtn = document.getElementById('torPrevPageBtn');
    var nextBtn = document.getElementById('torNextPageBtn');
    if (indicator) indicator.textContent = torCurrentPage + ' / ' + totalPages;
    if (prevBtn) prevBtn.disabled = torCurrentPage <= 1;
    if (nextBtn) nextBtn.disabled = torCurrentPage >= totalPages;

    var wrap = document.querySelector('#torPreviewModal .tor-preview-wrap');
    if (wrap) {
        wrap.scrollTop = 0;
        wrap.scrollLeft = 0;
    }
}

function torOpenPreview(rowId) {
    var data = torGetRowData(rowId);
    if (!data) return;

    torCurrentData = data;
    torCurrentPage = 1;
    torRenderPreviewSheet(data);

    var modal = document.getElementById('torPreviewModal');
    if (modal) modal.style.display = 'flex';
    document.body.classList.add('tor-preview-open');
}

function torClosePreview() {
    var modal = document.getElementById('torPreviewModal');
    if (modal) modal.style.display = 'none';
    document.body.classList.remove('tor-preview-open');
}

function torNextPage() {
    if (!torCurrentData) return;
    torCurrentPage += 1;
    torRenderPreviewSheet(torCurrentData);
}

function torPrevPage() {
    if (!torCurrentData) return;
    torCurrentPage -= 1;
    torRenderPreviewSheet(torCurrentData);
}

function torPrintSheets(pages) {
    var container = document.getElementById('torPrintContainer');
    if (!container || !pages || !pages.length) return;

    container.innerHTML = pages.map(function(page, index) {
        var pageClass = ' tor-print-page';
        if (index < pages.length - 1) pageClass += ' tor-print-page-break';
        var breakStyle = index < pages.length - 1
            ? 'page-break-after:always; break-after:page;'
            : 'page-break-after:auto; break-after:auto;';
        return '<div class="tor-sheet' + pageClass + '" style="' + breakStyle + '">' + page + '</div>';
    }).join('');

    torRestorePreviewClass = document.body.classList.contains('tor-preview-open');
    var modal = document.getElementById('torPreviewModal');
    torRestorePreviewModal = !!(modal && modal.style.display === 'flex');
    if (modal) modal.style.display = 'none';
    document.body.classList.remove('tor-preview-open');
    document.body.classList.add('tor-printing');
    setTimeout(function() {
        window.print();
    }, 220);
}

function torPrintPreview() {
    if (!torCurrentData) return;
    torPrintSheets(torBuildPages(torCurrentData));
}

function torToggleSelectAll(source) {
    document.querySelectorAll('#torTableBody .tor-row-select').forEach(function(cb) {
        cb.checked = !!source.checked;
    });
    torSyncSelectAll();
}

function torSyncSelectAll() {
    var header = document.getElementById('torSelectAll');
    var items = document.querySelectorAll('#torTableBody .tor-row-select');
    if (!header) return;

    var total = items.length;
    var checked = 0;
    items.forEach(function(cb) {
        if (cb.checked) checked++;
    });

    header.checked = total > 0 && checked === total;
    header.indeterminate = checked > 0 && checked < total;
}

function torCloseMenus() {
    document.querySelectorAll('.apst-dropdown.open').forEach(function(menu) {
        menu.classList.remove('open', 'drop-up');
        menu.style.top = '';
        menu.style.left = '';
        menu.style.right = '';
        menu.style.bottom = '';
    });
}

function torToggleMenu(menuId, trigger) {
    var menu = document.getElementById(menuId);
    if (!menu || !trigger) return;

    var isOpen = menu.classList.contains('open');
    torCloseMenus();
    if (isOpen) return;

    var rect = trigger.getBoundingClientRect();
    var spaceBelow = window.innerHeight - rect.bottom;
    menu.style.left = 'auto';
    menu.style.right = (window.innerWidth - rect.left + 4) + 'px';

    if (spaceBelow < 120) {
        menu.classList.add('drop-up');
        menu.style.top = 'auto';
        menu.style.bottom = (window.innerHeight - rect.bottom) + 'px';
    } else {
        menu.style.top = rect.top + 'px';
        menu.style.bottom = 'auto';
    }

    menu.classList.add('open');
}

function torOpenModal(id) {
    torCloseMenus();
    var modal = document.getElementById(id);
    if (modal) modal.style.display = 'flex';
}

function torCloseModal(id) {
    var modal = document.getElementById(id);
    if (modal) modal.style.display = 'none';
}

function torGetRow(rowId) {
    return document.querySelector('tr[data-row-id="' + rowId + '"]');
}

function torOpenEdit(rowId) {
    var row = torGetRow(rowId);
    if (!row) return;

    torCurrentRowId = rowId;
    var cells = row.querySelectorAll('td');
    document.getElementById('torEditNumber').value = (cells[1] ? cells[1].textContent : '').trim();
    document.getElementById('torEditName').value = (cells[2] ? cells[2].textContent : '').trim();
    document.getElementById('torEditCourse').value = (cells[3] ? cells[3].textContent : '').trim();
    document.getElementById('torEditYear').value = (cells[4] ? cells[4].textContent : '').trim();
    torOpenModal('torEditModal');
}

function torSaveEdit() {
    var row = torGetRow(torCurrentRowId);
    if (!row) return;
    var cells = row.querySelectorAll('td');

    if (cells[1]) cells[1].textContent = (document.getElementById('torEditNumber').value || '').trim();
    if (cells[2]) cells[2].textContent = (document.getElementById('torEditName').value || '').trim();
    if (cells[3]) cells[3].textContent = (document.getElementById('torEditCourse').value || '').trim();
    if (cells[4]) cells[4].textContent = (document.getElementById('torEditYear').value || '').trim();

    torCloseModal('torEditModal');
}

function torOpenDelete(rowId) {
    torCurrentRowId = rowId;
    torOpenModal('torDeleteModal');
}

function torConfirmDelete() {
    var row = torGetRow(torCurrentRowId);
    if (row) row.remove();
    torSyncSelectAll();
    torCloseModal('torDeleteModal');
}

document.addEventListener('click', function(event) {
    var toggle = event.target.closest('[data-tor-menu-toggle]');
    if (toggle) {
        event.stopPropagation();
        torToggleMenu(toggle.getAttribute('data-tor-menu-toggle'), toggle);
        return;
    }

    if (!event.target.closest('.apst-dropdown')) {
        torCloseMenus();
    }
});

window.addEventListener('scroll', torCloseMenus, true);

window.addEventListener('afterprint', function() {
    document.body.classList.remove('tor-printing');
    var modal = document.getElementById('torPreviewModal');
    if (torRestorePreviewModal && modal) {
        modal.style.display = 'flex';
    }
    if (torRestorePreviewClass && modal && modal.style.display === 'flex') {
        document.body.classList.add('tor-preview-open');
    }
    torRestorePreviewClass = false;
    torRestorePreviewModal = false;
    var container = document.getElementById('torPrintContainer');
    if (container) container.innerHTML = '';
});
