<div class="flex justify-between items-center mb-4">
    <form action="{{ route('admin.add-customer-vehicles.index') }}" method="GET" class="flex items-center space-x-2">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search Vehicle..."
               class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500">
        <button type="submit" class="bg-sky-600 text-white px-4 py-2 rounded-lg hover:bg-sky-700">Search</button>
    </form>

    <a href="{{ route('admin.add-customer-vehicles.create') }}"
       class="bg-green-600 text-white px-4 py-2 rounded-lg shadow hover:bg-green-700">
       + Add Vehicle
    </a>
</div>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EEMOTRACK - Offer Letter</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Small print-friendly tweaks */
    @media print {
      .no-print { display: none !important; }
      body { background: white; }
    }
  </style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
  <div class="max-w-4xl mx-auto p-6">
    <div class="bg-white shadow rounded-lg overflow-hidden">
      <div class="flex items-center justify-between p-6 border-b">
        <div class="flex items-center gap-4">
          <div class="w-16 h-16 rounded-md bg-gradient-to-br from-sky-600 to-indigo-600 flex items-center justify-center text-white font-bold">ET</div>
          <div>
            <h1 class="text-xl font-semibold">EEMOTRACK</h1>
            <p class="text-sm text-gray-500">Real-time GPS Tracking & Fleet Solutions</p>
          </div>
        </div>
        <div class="text-right text-sm text-gray-600">
          <p>Offer Date: <span id="offerDate"></span></p>
          <p>Offer ID: <span id="offerId">ET-<span class="font-mono">XXXX</span></span></p>
        </div>
      </div>

      <div class="p-6 space-y-6">
        <section>
          <h2 class="text-lg font-medium">To,</h2>
          <p class="mt-1 text-sm text-gray-700"> <strong id="clientName">[Client Name]</strong><br>
          <span id="clientCompany">[Company / Organization]</span><br>
          <span id="clientAddress" class="text-xs text-gray-500">[Address]</span></p>
        </section>

        <section>
          <h3 class="text-base font-semibold">Subject: Offer for GPS Tracking & Fleet Management Solution</h3>
          <p class="mt-3 text-sm text-gray-700">Dear <span id="clientShort">[Client]</span>,</p>
          <p class="mt-2 text-sm text-gray-700">We are pleased to submit this offer for implementing our GPS tracking and fleet management solution tailored to your needs. Below is the recommended package and pricing. This offer is valid for <strong>15 days</strong> from the date above.</p>
        </section>

        <section class="overflow-x-auto">
          <table class="w-full text-sm border-collapse">
            <thead>
              <tr class="bg-gray-100 text-gray-700">
                <th class="p-3 text-left">Package</th>
                <th class="p-3 text-left">Features</th>
                <th class="p-3 text-right">Price / Unit</th>
                <th class="p-3 text-right">Subscription</th>
              </tr>
            </thead>
            <tbody>
              <tr class="border-t">
                <td class="p-3 font-medium">Pro Fleet</td>
                <td class="p-3">Real-time tracking, driver behaviour, route optimization, reports</td>
                <td class="p-3 text-right">₹12,000</td>
                <td class="p-3 text-right">/month</td>
              </tr>
              <tr class="border-t bg-gray-50">
                <td class="p-3 font-medium">Basic Monitor</td>
                <td class="p-3">Live location, geofence alerts, basic reports</td>
                <td class="p-3 text-right">₹3,500</td>
                <td class="p-3 text-right">/month</td>
              </tr>
              <tr class="border-t">
                <td class="p-3 font-medium">Add-ons</td>
                <td class="p-3">4G Dashcam, AC & Door Sensors, SOS button</td>
                <td class="p-3 text-right">₹5,000</td>
                <td class="p-3 text-right">one-time</td>
              </tr>
            </tbody>
          </table>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="p-4 bg-gray-50 rounded">
            <h4 class="font-semibold text-sm">Payment Terms</h4>
            <ul class="mt-2 text-sm space-y-1 text-gray-700">
              <li>• 50% upfront at order placement</li>
              <li>• 50% before deployment</li>
              <li>• Annual plans: 1 month free on yearly prepay</li>
            </ul>
          </div>

          <div class="p-4 bg-gray-50 rounded">
            <h4 class="font-semibold text-sm">Support & Warranty</h4>
            <p class="mt-2 text-sm text-gray-700">Includes 12 months hardware warranty and premium support (phone + email). SLA response within 24 hours for critical issues.</p>
          </div>
        </section>

        <section>
          <h4 class="font-semibold">Validity & Acceptance</h4>
          <p class="mt-2 text-sm text-gray-700">This offer is valid until <span id="validUntil"></span>. To accept, please sign below and return a scanned copy or send an acceptance email to <a href="mailto:info@eemotrack.com" class="text-sky-600">info@eemotrack.com</a>.</p>
        </section>

        <section class="mt-4">
          <div class="flex items-center justify-between gap-6">
            <div class="flex-1">
              <p class="text-sm text-gray-700">For <strong>EEMOTRACK</strong></p>
              <div class="mt-6">
                <p class="text-sm">__________________________</p>
                <p class="text-sm font-medium">[Authorised Signatory]</p>
                <p class="text-xs text-gray-500">Designation</p>
              </div>
            </div>

            <div class="flex-1">
              <p class="text-sm text-gray-700">Accepted By</p>
              <div class="mt-6">
                <p class="text-sm">__________________________</p>
                <p class="text-sm font-medium">[Client Name]</p>
                <p class="text-xs text-gray-500">Date: __________</p>
              </div>
            </div>
          </div>
        </section>

        <div class="mt-6 flex items-center gap-3 no-print">
          <button id="printBtn" class="px-4 py-2 bg-sky-600 text-white rounded shadow">Print / Save PDF</button>
          <button id="downloadBtn" class="px-4 py-2 border border-sky-600 text-sky-600 rounded">Download HTML</button>
          <button id="fillSample" class="px-4 py-2 bg-gray-100 rounded">Fill Sample Data</button>
        </div>

      </div>

      <div class="p-4 bg-gray-50 text-xs text-gray-500 border-t">This is a computer-generated offer and does not require a physical signature unless requested.</div>
    </div>
  </div>

  <script>
    // Small helpers to fill dynamic fields
    const offerDateEl = document.getElementById('offerDate');
    const validUntilEl = document.getElementById('validUntil');
    const offerIdEl = document.getElementById('offerId');

    const today = new Date();
    const dd = String(today.getDate()).padStart(2,'0');
    const mm = String(today.getMonth()+1).padStart(2,'0');
    const yyyy = today.getFullYear();
    offerDateEl.textContent = dd + '-' + mm + '-' + yyyy;

    const valid = new Date(today);
    valid.setDate(valid.getDate() + 15);
    const vdd = String(valid.getDate()).padStart(2,'0');
    const vmm = String(valid.getMonth()+1).padStart(2,'0');
    const vyyyy = valid.getFullYear();
    validUntilEl.textContent = vdd + '-' + vmm + '-' + vyyyy;

    // generate a quick offer id
    offerIdEl.querySelector('.font-mono').textContent = Math.floor(1000 + Math.random()*9000);

    document.getElementById('printBtn').addEventListener('click', () => window.print());

    // download HTML as file
    document.getElementById('downloadBtn').addEventListener('click', () => {
      const blob = new Blob([document.documentElement.outerHTML], {type: 'text/html'});
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url; a.download = 'eemotrack-offer.html';
      document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
    });

    // sample filler for quick preview
    document.getElementById('fillSample').addEventListener('click', () => {
      document.getElementById('clientName').textContent = 'Mr. Rajesh Kumar';
      document.getElementById('clientCompany').textContent = 'Shree Logistics Pvt Ltd';
      document.getElementById('clientAddress').textContent = 'Plot 12, Phase II, Industrial Area, Pune, Maharashtra';
      document.getElementById('clientShort').textContent = 'Rajesh';
    });
  </script>
</body>
</html>
