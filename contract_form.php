<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DJ Star Roofing & Construction - Contract Form</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative;
            min-height: 100vh;
        }

        .page {
            padding: 30px;
            display: none;
        }

        .page.active {
            display: block;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .company-info {
            margin-bottom: 20px;
            font-size: 14px;
        }

        .offers {
            margin: 20px 0;
            padding-left: 20px;
        }

        .offers li {
            margin-bottom: 5px;
        }

        .form-section {
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"], input[type="date"], textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .signature-area {
            margin-top: 40px;
            border-top: 1px solid #333;
            padding-top: 20px;
        }

        .signature-line {
            margin-top: 50px;
            border-top: 1px solid #333;
            width: 70%;
        }

        .navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding: 10px 30px;
            background-color: #f9f9f9;
            border-top: 1px solid #ddd;
        }

        button {
            padding: 10px 20px;
            background-color: #2c3e50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #1a252f;
        }

        button:disabled {
            background-color: #95a5a6;
            cursor: not-allowed;
        }

        .page-indicator {
            text-align: center;
            margin-top: 10px;
            font-size: 14px;
            color: #7f8c8d;
        }

        .important-notice {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }

        .terms-list {
            padding-left: 20px;
            margin: 15px 0;
        }

        .terms-list li {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Page 1: Opening Page -->
        <div class="page active" id="page1">
            <div class="header">
                <h1>DJ STAR ROOFING AND CONSTRUCTION</h1>
                <div class="company-info">
                    <p>Main Office: Maharilla Hi-way Sumacab Sur, Cabanatuan City</p>
                    <p>Cel. Nos. 0908-620-2813 / 0917-659-9245</p>
                    <p>Tel. No. (044) 960-1706 Email Address: starrodfng_3@gmail.com</p>
                </div>
            </div>

            <div class="important-notice">
                <h2>INSTALLATION INCLUDED: IMPORTANT</h2>
                <p>All specifications on Reference Quotation shall be provided. AGREEMENT ON MATERIALS: Any Insufficiency on quantity will be provided without change, except for uncoordinated changes on site, any property of the state or its parent or member as a case may do in accordance with the criteria of Chapter 1 Constructor.</p>
            </div>

            <div class="form-section">
                <h2>We also offer:</h2>
                <ul class="offers">
                    <li>Steel Trusses & all kinds of roofing</li>
                    <li>Design & Construction</li>
                    <li>Roll up Doors & Stainless railings</li>
                    <li><strong>UPVC & Aluminum doors & windows</strong></li>
                    <li>Hardware accessories, insulators, Decking, Purlins</li>
                    <li>Korean Window blinds</li>
                </ul>
            </div>

            <div class="header">
                <h1>CONTRACT CONFIRMATION</h1>
                <p>This amends our agreement as stated in Sales Quotation No. ______ Dated: ______</p>
            </div>

            <div class="form-section">
                <h2>RESIDENTIAL ADDRESS</h2>
                <div class="form-group">
                    <label for="project-description">PROJECT DESCRIPTION:</label>
                    <input type="text" id="project-description" name="project-description">
                </div>
                <div class="form-group">
                    <label for="project-location">PROJECT LOCATION:</label>
                    <input type="text" id="project-location" name="project-location">
                </div>
                <div class="form-group">
                    <label for="contact-no">CONTACT NO.:</label>
                    <input type="text" id="contact-no" name="contact-no">
                </div>
                <div class="form-group">
                    <label for="contact-person">Contact Person, if any:</label>
                    <input type="text" id="contact-person" name="contact-person">
                </div>
                <p>This is to confirm the following contract amount with the corresponding agreed terms and conditions below and other specifications stated in Annex ______.</p>
            </div>
        </div>

        <!-- Page 2: Contract Amount -->
        <div class="page" id="page2">
            <div class="header">
                <h1>I. CONTRACT AMOUNT</h1>
            </div>

            <div class="form-section">
                <div class="form-group">
                    <label for="total-materials">Total Amount of materials:</label>
                    <input type="text" id="total-materials" name="total-materials">
                </div>
                <div class="form-group">
                    <label for="installation">Installation:</label>
                    <input type="text" id="installation" name="installation">
                </div>
                <div class="form-group">
                    <label for="delivery-charge">Delivery Charge:</label>
                    <input type="text" id="delivery-charge" name="delivery-charge">
                </div>
                <div class="form-group">
                    <label for="total-contract-amount">TOTAL CONTRACT AMOUNT:</label>
                    <input type="text" id="total-contract-amount" name="total-contract-amount">
                </div>
                <div class="form-group">
                    <label for="remarks">Remarks/Other Details:</label>
                    <textarea id="remarks" name="remarks" rows="4"></textarea>
                </div>
            </div>
        </div>

        <!-- Page 3: Terms of Payment -->
        <div class="page" id="page3">
            <div class="header">
                <h1>II. TERMS OF PAYMENT</h1>
            </div>

            <table>
                <tr>
                    <th></th>
                    <th>Details of downpayment:</th>
                    <th>FOR FINANCE USE ONLY:</th>
                </tr>
                <tr>
                    <td>Down payment:</td>
                    <td>
                        <label for="cash">Cash:</label>
                        <input type="text" id="cash" name="cash">
                    </td>
                    <td rowspan="4">
                        <p>downpayment received</p>
                        <div class="form-group">
                            <label for="received-from">From:</label>
                            <input type="text" id="received-from" name="received-from">
                        </div>
                        <div class="form-group">
                            <label for="received-by">By:</label>
                            <input type="text" id="received-by" name="received-by">
                        </div>
                        <div class="form-group">
                            <label for="received-date">Date:</label>
                            <input type="date" id="received-date" name="received-date">
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>Upon Delivery/Pick up:</td>
                    <td>
                        <label for="check-amt">Check Amt:</label>
                        <input type="text" id="check-amt" name="check-amt">
                    </td>
                </tr>
                <tr>
                    <td>Upon Completion:</td>
                    <td>
                        <label for="check-date">Date:</label>
                        <input type="date" id="check-date" name="check-date">
                    </td>
                </tr>
                <tr>
                    <td>Outstanding Balance:</td>
                    <td>
                        <label for="bank-check-no">Bank & Check No.</label>
                        <input type="text" id="bank-check-no" name="bank-check-no">
                    </td>
                </tr>
            </table>

            <div class="important-notice">
                <p><strong>NO PAYMENT, NO UNLOADING, IN CASE OF 2nd DELIVERY, ADDITIONAL CHARGE WILL BE IMPOSED</strong></p>
            </div>
        </div>

        <!-- Page 4: Terms and Conditions for Check Payment -->
        <div class="page" id="page4">
            <div class="header">
                <h1>TERMS AND CONDITIONS FOR CHECK PAYMENT:</h1>
            </div>

            <ol class="terms-list">
                <li>For check payments, purchase order shall only proceed upon clearing of checks.</li>
                <li>For SUPPLY ONLY, CASH PAYMENT UPON DELIVERY IS REQUIRED. If payment will be via CHECK, check payment must be made prior to the delivery schedule for clearing purposes.</li>
            </ol>

            <div class="important-notice">
                <p>PLEASE MAKE ALL CHECK PAYMENTS PAYABLE TO: DJ STAR ROOFING AND CONSTRUCTION</p>
            </div>
        </div>

        <!-- Page 5: Important and Conforme -->
        <div class="page" id="page5">
            <div class="header">
                <h1>IMPORTANT</h1>
            </div>

            <div class="important-notice">
                <p>Above goods remain the property of DJ Star Roofing & Construction until fully paid. Check payments that fail to clear upon presentment shall be charged P500.00 per check and a 30% annual interest based on unpaid balance. In case of non-payment of outstanding balance upon completion/delivery, full authority is hereby given to implement policies and procedures, not limited to the pull-out of materials and/or resort to legal process, if necessary.</p>
            </div>

            <div class="signature-area">
                <h2>CONFORME:</h2>
                <p>I hereby attest that I have read and fully understand the Terms of Acceptance as provided in the dorsal side hereof and I agree to all the provisions stated therein.</p>

                <div class="form-group">
                    <div class="signature-line"></div>
                    <label>Client's Signature over printed name</label>
                </div>

                <div class="form-group">
                    <div class="signature-line"></div>
                    <label>(Authorized Representative of DJSRC)</label>
                </div>

                <p style="text-align: right; margin-top: 30px;">(updated form 11-2019)</p>
            </div>
        </div>

        <!-- Navigation -->
        <div class="navigation">
            <button id="prev-btn" disabled>Previous</button>
            <div class="page-indicator">Page <span id="current-page">1</span> of 5</div>
            <button id="next-btn">Next</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const pages = document.querySelectorAll('.page');
            const prevBtn = document.getElementById('prev-btn');
            const nextBtn = document.getElementById('next-btn');
            const currentPageSpan = document.getElementById('current-page');
            let currentPage = 0;

            function updateNavigation() {
                // Update page indicator
                currentPageSpan.textContent = currentPage + 1;
                
                // Update button states
                prevBtn.disabled = currentPage === 0;
                nextBtn.textContent = currentPage === pages.length - 1 ? 'Submit' : 'Next';
                
                // Show/hide pages
                pages.forEach((page, index) => {
                    if (index === currentPage) {
                        page.classList.add('active');
                    } else {
                        page.classList.remove('active');
                    }
                });
            }

            prevBtn.addEventListener('click', function() {
                if (currentPage > 0) {
                    currentPage--;
                    updateNavigation();
                }
            });

            nextBtn.addEventListener('click', function() {
                if (currentPage < pages.length - 1) {
                    currentPage++;
                    updateNavigation();
                } else {
                    // Submit form logic here
                    alert('Form submitted! In a real application, this would save the data.');
                }
            });

            // Initialize
            updateNavigation();
        });
    </script>
</body>
</html>