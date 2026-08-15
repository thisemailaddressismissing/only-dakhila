<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('নতুন দাখিলা যোগ করুন') }}
        </h2>
    </x-slot>

    <div class="container mx-auto px-4 py-8 position-relative">
        <!-- Glow Orbs -->
        <div style="position: fixed; top: -150px; left: -150px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(102,126,234,0.15) 0%, transparent 70%); pointer-events: none; z-index: 0;"></div>
        <div style="position: fixed; bottom: -100px; right: -100px; width: 350px; height: 350px; background: radial-gradient(circle, rgba(232,62,140,0.1) 0%, transparent 70%); pointer-events: none; z-index: 0;"></div>
        <div style="position: fixed; top: 40%; left: -80px; width: 250px; height: 250px; background: radial-gradient(circle, rgba(118,75,162,0.1) 0%, transparent 70%); pointer-events: none; z-index: 0;"></div>

        <div class="max-w-7xl mx-auto position-relative" style="z-index: 1;">
            <!-- Header -->
            <div class="text-center mb-4">
                <h1 class="fw-bold mb-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #e83e8c 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: 1.8rem;">
                    📄 নতুন দাখিলা যোগ করুন
                </h1>
                <p style="color: #8b7ba8; font-size: 0.95rem;">দাখিলার তথ্য সংরক্ষণ করুন</p>
            </div>

            <!-- Error Messages -->
            @if ($errors->any())
                <div class="mb-4 p-3 rounded-4" style="background: rgba(239,68,68,0.12); backdrop-filter: blur(10px); border: 1.5px solid rgba(239,68,68,0.3);">
                    <div class="d-flex align-items-center mb-2">
                        <svg style="width:20px;height:20px;color:#dc2626;margin-right:10px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <strong style="color: #991b1b;">অনুগ্রহ করে নিম্নলিখিত ত্রুটিগুলি সংশোধন করুন:</strong>
                    </div>
                    <ul class="mb-0 ps-4" style="color: #991b1b; font-size:0.85rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(isset($banglaMake))
                <div class="mb-4 p-3 rounded-4" style="background: rgba(102,126,234,0.1); backdrop-filter: blur(10px); border: 1.5px solid rgba(102,126,234,0.2);">
                    <span style="color: #667eea; font-weight: 600; font-size:0.9rem;">
                        <i class="fas fa-info-circle me-2"></i> প্রতি অনুসন্ধানের জন্য {{ $banglaMake }} টাকা কাটা হবে
                    </span>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-4 p-3 rounded-4 d-flex align-items-center" style="background: rgba(34,197,94,0.12); backdrop-filter: blur(10px); border: 1.5px solid rgba(34,197,94,0.3);">
                    <svg style="width:20px;height:20px;color:#16a34a;margin-right:10px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span style="color: #166534; font-weight: 600; font-size:0.9rem;">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-3 rounded-4 d-flex align-items-center" style="background: rgba(239,68,68,0.12); backdrop-filter: blur(10px); border: 1.5px solid rgba(239,68,68,0.3);">
                    <svg style="width:20px;height:20px;color:#dc2626;margin-right:10px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span style="color: #991b1b; font-weight: 600; font-size:0.9rem;">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Form Card -->
            <div class="rounded-4 overflow-hidden p-4 p-lg-5" style="background: linear-gradient(145deg, rgba(255,255,255,0.92), rgba(240,225,255,0.88), rgba(220,200,250,0.82), rgba(245,235,255,0.9)); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1.5px solid rgba(102,126,234,0.25); box-shadow: 0 15px 50px rgba(102,126,234,0.12), 0 0 0 1px rgba(255,255,255,0.5) inset;">
                <form action="{{ route('user.dashboard.dakhila.store') }}" method="POST">
                    @csrf

                    <!-- Glossy Form Input Styles -->
                    @php
                    $inputStyle = 'border: 1.5px solid rgba(102,126,234,0.2); background: rgba(255,255,255,0.8); backdrop-filter: blur(5px); box-shadow: 0 2px 8px rgba(0,0,0,0.04) inset; padding: 12px 16px; transition: all 0.3s ease;';
                    $selectStyle = 'border: 1.5px solid rgba(102,126,234,0.2); background: rgba(255,255,255,0.8); backdrop-filter: blur(5px); padding: 12px 16px; transition: all 0.3s ease;';
                    $focusOn = "this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102,126,234,0.15), 0 2px 8px rgba(0,0,0,0.04) inset';";
                    $focusOnSelect = "this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102,126,234,0.15)';";
                    $focusOff = "this.style.borderColor='rgba(102,126,234,0.2)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.04) inset';";
                    $focusOffSelect = "this.style.borderColor='rgba(102,126,234,0.2)'; this.style.boxShadow='none';";
                    $labelStyle = 'color: #4a3f6b; font-size: 0.85rem;';
                    @endphp

                    <div class="d-flex flex-column gap-3">
                        <!-- First Row (5 cols) -->
                        <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 1rem;">
                            <div>
                                <label class="form-label fw-semibold mb-2 d-block" style="{{ $labelStyle }}">রেজিস্ট্রি নম্বর</label>
                                <input type="text" name="registry_no" value="{{ old('registry_no') }}" required class="form-control rounded-3 w-100" style="{{ $inputStyle }}" onfocus="{{ $focusOn }}" onblur="{{ $focusOff }}">
                            </div>
                            <div>
                                <label class="form-label fw-semibold mb-2 d-block" style="{{ $labelStyle }}">চালান নম্বর (ঐচ্ছিক)</label>
                                <input type="text" name="challan_no" value="{{ old('challan_no') }}" class="form-control rounded-3 w-100" style="{{ $inputStyle }}" onfocus="{{ $focusOn }}" onblur="{{ $focusOff }}">
                            </div>
                            <div style="grid-column: span 2;">
                                <label class="form-label fw-semibold mb-2 d-block" style="{{ $labelStyle }}">সিটি কর্পোরেশন / পৌরসভা / ইউনিয়ন ভূমি অফিসের নাম</label>
                                <input type="text" name="office_name" value="{{ old('office_name') }}" required class="form-control rounded-3 w-100" style="{{ $inputStyle }}" onfocus="{{ $focusOn }}" onblur="{{ $focusOff }}">
                            </div>
                            <div>
                                <label class="form-label fw-semibold mb-2 d-block" style="{{ $labelStyle }}">উপজেলা / থানা</label>
                                <input type="text" name="upazila" value="{{ old('upazila') }}" required class="form-control rounded-3 w-100" style="{{ $inputStyle }}" onfocus="{{ $focusOn }}" onblur="{{ $focusOff }}">
                            </div>
                        </div>

                        <!-- Second Row (5 cols) -->
                        <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 1rem;">
                            <div>
                                <label class="form-label fw-semibold mb-2 d-block" style="{{ $labelStyle }}">জেলা</label>
                                <input type="text" name="district" value="{{ old('district') }}" required class="form-control rounded-3 w-100" style="{{ $inputStyle }}" onfocus="{{ $focusOn }}" onblur="{{ $focusOff }}">
                            </div>
                            <div style="grid-column: span 2;">
                                <label class="form-label fw-semibold mb-2 d-block" style="{{ $labelStyle }}">২ নং রেজিস্টার অনুযায়ী হোল্ডিং নম্বর</label>
                                <input type="text" name="holding_no" value="{{ old('holding_no') }}" required class="form-control rounded-3 w-100" style="{{ $inputStyle }}" onfocus="{{ $focusOn }}" onblur="{{ $focusOff }}">
                            </div>
                            <div style="grid-column: span 2;">
                                <label class="form-label fw-semibold mb-2 d-block" style="{{ $labelStyle }}">মৌজা ও জে. এল. নম্বর</label>
                                <input type="text" name="mouja_jl" value="{{ old('mouja_jl') }}" required class="form-control rounded-3 w-100" style="{{ $inputStyle }}" onfocus="{{ $focusOn }}" onblur="{{ $focusOff }}">
                            </div>
                        </div>

                        <!-- Third Row (5 cols) -->
                        <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 1rem;">
                            <div>
                                <label class="form-label fw-semibold mb-2 d-block" style="{{ $labelStyle }}">খতিয়ান নম্বর</label>
                                <input type="text" name="khatian_no" value="{{ old('khatian_no') }}" required class="form-control rounded-3 w-100" style="{{ $inputStyle }}" onfocus="{{ $focusOn }}" onblur="{{ $focusOff }}">
                            </div>
                            <div>
                                <label class="form-label fw-semibold mb-2 d-block" style="{{ $labelStyle }}">পরিশোধের সাল (BN)</label>
                                <input type="text" name="payment_year_bn" id="payment_year_bn" value="{{ old('payment_year_bn') }}" required class="form-control rounded-3 w-100" style="{{ $inputStyle }}" onfocus="{{ $focusOn }}" onblur="{{ $focusOff }}">
                            </div>
                            <div>
                                <label class="form-label fw-semibold mb-2 d-block" style="{{ $labelStyle }}">পরিশোধের তারিখ (EN)</label>
                                <input type="text" name="payment_year_en" id="payment_year_en" value="{{ old('payment_year_en') }}" required class="form-control rounded-3 w-100" style="{{ $inputStyle }}" onfocus="{{ $focusOn }}" onblur="{{ $focusOff }}">
                            </div>
                            <div style="grid-column: span 2;">
                                <label class="form-label fw-semibold mb-2 d-block" style="{{ $labelStyle }}">দিন মাস বছর</label>
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem;">
                                    <div>
                                        <select name="day" required class="form-select rounded-3 w-100" style="{{ $selectStyle }}" onfocus="{{ $focusOnSelect }}" onblur="{{ $focusOffSelect }}">
                                            <option value="">--নির্বাচন--</option>
                                            @for ($i = 1; $i <= 31; $i++)
                                                <option value="{{ $i }}" {{ old('day') == $i ? 'selected' : '' }}>
                                                    {{ app('App\Helpers\NumberConverter')->englishToBangla($i) }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div>
                                        <select name="month" required class="form-select rounded-3 w-100" style="{{ $selectStyle }}" onfocus="{{ $focusOnSelect }}" onblur="{{ $focusOffSelect }}">
                                            <option value="">--নির্বাচন--</option>
                                            <option value="1" {{ old('month') == 1 ? 'selected' : '' }}>বৈশাখ</option>
                                            <option value="2" {{ old('month') == 2 ? 'selected' : '' }}>জ্যৈষ্ঠ</option>
                                            <option value="3" {{ old('month') == 3 ? 'selected' : '' }}>আষাঢ়</option>
                                            <option value="4" {{ old('month') == 4 ? 'selected' : '' }}>শ্রাবণ</option>
                                            <option value="5" {{ old('month') == 5 ? 'selected' : '' }}>ভাদ্র</option>
                                            <option value="6" {{ old('month') == 6 ? 'selected' : '' }}>আশ্বিন</option>
                                            <option value="7" {{ old('month') == 7 ? 'selected' : '' }}>কার্তিক</option>
                                            <option value="8" {{ old('month') == 8 ? 'selected' : '' }}>অগ্রহায়ণ</option>
                                            <option value="9" {{ old('month') == 9 ? 'selected' : '' }}>পৌষ</option>
                                            <option value="10" {{ old('month') == 10 ? 'selected' : '' }}>মাঘ</option>
                                            <option value="11" {{ old('month') == 11 ? 'selected' : '' }}>ফাল্গুন</option>
                                            <option value="12" {{ old('month') == 12 ? 'selected' : '' }}>চৈত্র</option>
                                        </select>
                                    </div>
                                    <div>
                                        <select name="year" id="year" required class="form-select rounded-3 w-100" style="{{ $selectStyle }}" onfocus="{{ $focusOnSelect }}" onblur="{{ $focusOffSelect }}">
                                            <option value="">--নির্বাচন--</option>
                                            @php
                                                for($i = 1400; $i <= 1500; $i++) {
                                                    $bengaliNumber = app('App\Helpers\NumberConverter')->englishToBangla($i);
                                                    $selected = old('year') == $i ? 'selected' : '';
                                                    echo "<option value=\"$i\" $selected>$bengaliNumber</option>\n";
                                                }
                                            @endphp
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information (4 cols) -->
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-2 d-block" style="{{ $labelStyle }}">তিন বৎসরের ঊর্ধ্বের বকেয়া (EN)</label>
                                <input type="number" name="three_years_plus_due" value="{{ old('three_years_plus_due') }}" step="0.01" required class="form-control rounded-3" style="{{ $inputStyle }}" onfocus="{{ $focusOn }}" onblur="{{ $focusOff }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-2 d-block" style="{{ $labelStyle }}">গত তিন বৎসরের বকেয়া (EN)</label>
                                <input type="number" name="last_three_years_due" value="{{ old('last_three_years_due') }}" step="0.01" required class="form-control rounded-3" style="{{ $inputStyle }}" onfocus="{{ $focusOn }}" onblur="{{ $focusOff }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-2 d-block" style="{{ $labelStyle }}">বকেয়ার সুদ ও ক্ষতিপূরণ (EN)</label>
                                <input type="number" name="due_interest" value="{{ old('due_interest') }}" step="0.01" required class="form-control rounded-3" style="{{ $inputStyle }}" onfocus="{{ $focusOn }}" onblur="{{ $focusOff }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-2 d-block" style="{{ $labelStyle }}">হাল দাবি (EN)</label>
                                <input type="number" name="current_demand" value="{{ old('current_demand') }}" step="0.01" required class="form-control rounded-3" style="{{ $inputStyle }}" onfocus="{{ $focusOn }}" onblur="{{ $focusOff }}">
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-2 d-block" style="{{ $labelStyle }}">মোট দাবি (EN)</label>
                                <input type="number" name="total_demand" value="{{ old('total_demand') }}" step="0.01" required class="form-control rounded-3" style="{{ $inputStyle }}" onfocus="{{ $focusOn }}" onblur="{{ $focusOff }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-2 d-block" style="{{ $labelStyle }}">মোট আদায় (EN)</label>
                                <input type="number" name="total_collection" value="{{ old('total_collection') }}" step="0.01" required class="form-control rounded-3" style="{{ $inputStyle }}" onfocus="{{ $focusOn }}" onblur="{{ $focusOff }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-2 d-block" style="{{ $labelStyle }}">মোট বকেয়া (EN)</label>
                                <input type="number" name="total_due" value="{{ old('total_due') }}" step="0.01" required class="form-control rounded-3" style="{{ $inputStyle }}" onfocus="{{ $focusOn }}" onblur="{{ $focusOff }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-2 d-block" style="{{ $labelStyle }}">মন্তব্য</label>
                                <textarea name="comments" rows="1" class="form-control rounded-3" style="{{ $inputStyle }}" onfocus="{{ $focusOn }}" onblur="{{ $focusOff }}">{{ old('comments') }}</textarea>
                            </div>
                        </div>

                        <!-- Owner Section -->
                        <div class="mt-3">
                            <div class="d-flex justify-content-between align-items-center p-3 rounded-top-4" style="background: linear-gradient(135deg, rgba(102,126,234,0.25), rgba(118,75,162,0.2)); border: 1.5px solid rgba(102,126,234,0.3);">
                                <h5 class="m-0 text-white fw-bold" style="font-size: 1rem;">👤 মালিকের নাম ও সম্পত্তির পরিমাণ</h5>
                                <button type="button" class="btn btn-sm fw-bold rounded-circle d-flex align-items-center justify-content-center" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(5px); border: 1.5px solid rgba(255,255,255,0.3); color: white; width: 32px; height: 32px; font-size: 1.2rem; transition: all 0.3s ease;" onmouseover="this.style.background='rgba(255,255,255,0.35)'; this.style.transform='scale(1.1)';" onmouseout="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='scale(1)';" onclick="addOwnerRow()">+</button>
                            </div>
                            <div class="p-3" style="background: rgba(255,255,255,0.35); border: 1.5px solid rgba(102,126,234,0.2); border-top: none; border-radius: 0 0 16px 16px;">
                                <!-- Permanent first owner row -->
                                <div class="d-flex flex-wrap align-items-center gap-3 owner-row mb-2 p-3 rounded-3" style="background: rgba(255,255,255,0.7); backdrop-filter: blur(5px); border: 1.5px solid rgba(102,126,234,0.15);">
                                    <input type="text" class="form-control rounded-3 flex-1" name="owners[0][name]" value="{{ old('owners.0.name') }}" placeholder="মালিকের নাম" required style="{{ $inputStyle }}" onfocus="{{ $focusOn }}" onblur="{{ $focusOff }}">
                                    <input type="number" step="any" class="form-control rounded-3 flex-1" name="owners[0][share]" value="{{ old('owners.0.share') }}" placeholder="সম্পত্তির পরিমাণ (ইউজার)" required style="{{ $inputStyle }}" onfocus="{{ $focusOn }}" onblur="{{ $focusOff }}">
                                </div>
                                <div id="ownerSection" class="d-flex flex-column gap-2">
                                    @if(old('owners'))
                                        @foreach(old('owners') as $index => $owner)
                                            @if($index > 0)
                                                <div class="d-flex flex-wrap align-items-center gap-2 owner-row p-3 rounded-3" style="background: rgba(255,255,255,0.7); backdrop-filter: blur(5px); border: 1.5px solid rgba(102,126,234,0.15);">
                                                    <input type="text" class="form-control rounded-3 flex-1" name="owners[{{ $index }}][name]" placeholder="মালিকের নাম" value="{{ $owner['name'] ?? '' }}" style="{{ $inputStyle }}" onfocus="{{ $focusOn }}" onblur="{{ $focusOff }}">
                                                    <input type="number" step="any" class="form-control rounded-3 flex-1" name="owners[{{ $index }}][share]" placeholder="সম্পত্তির পরিমাণ (ইউজার)" value="{{ $owner['share'] ?? '' }}" style="{{ $inputStyle }}" onfocus="{{ $focusOn }}" onblur="{{ $focusOff }}">
                                                    <button type="button" class="remove-btn btn btn-sm fw-bold text-white rounded-3" style="background: rgba(239,68,68,0.8); border: none; padding: 6px 14px;">×</button>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Dag Section -->
                        <div class="mt-3">
                            <div class="d-flex justify-content-between align-items-center p-3 rounded-top-4" style="background: linear-gradient(135deg, rgba(102,126,234,0.25), rgba(118,75,162,0.2)); border: 1.5px solid rgba(102,126,234,0.3);">
                                <h5 class="m-0 text-white fw-bold" style="font-size: 1rem;">📋 দাগের তথ্য</h5>
                                <button type="button" class="btn btn-sm fw-bold rounded-circle d-flex align-items-center justify-content-center" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(5px); border: 1.5px solid rgba(255,255,255,0.3); color: white; width: 32px; height: 32px; font-size: 1.2rem; transition: all 0.3s ease;" onmouseover="this.style.background='rgba(255,255,255,0.35)'; this.style.transform='scale(1.1)';" onmouseout="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='scale(1)';" onclick="addDagRow()">+</button>
                            </div>
                            <div class="p-3" style="background: rgba(255,255,255,0.35); border: 1.5px solid rgba(102,126,234,0.2); border-top: none; border-radius: 0 0 16px 16px;">
                                <!-- Permanent first dag row -->
                                <div class="d-flex flex-wrap align-items-center gap-3 dag-row mb-2 p-3 rounded-3" style="background: rgba(255,255,255,0.7); backdrop-filter: blur(5px); border: 1.5px solid rgba(102,126,234,0.15);">
                                    <input type="text" class="form-control rounded-3 flex-1" name="dags[0][dag]" value="{{ old('dags.0.dag') }}" placeholder="দাগ নম্বর" required style="{{ $inputStyle }}" onfocus="{{ $focusOn }}" onblur="{{ $focusOff }}">
                                    <input type="text" class="form-control rounded-3 flex-1" name="dags[0][type]" value="{{ old('dags.0.type') }}" placeholder="খতিয়ান শ্রেণি" required style="{{ $inputStyle }}" onfocus="{{ $focusOn }}" onblur="{{ $focusOff }}">
                                    <input type="text" class="form-control rounded-3 flex-1" name="dags[0][amount]" value="{{ old('dags.0.amount') }}" placeholder="খতিয়ান পরিমাণ (EN)" required style="{{ $inputStyle }}" onfocus="{{ $focusOn }}" onblur="{{ $focusOff }}">
                                </div>
                                <div id="dagSection" class="d-flex flex-column gap-2">
                                    @if(old('dags'))
                                        @foreach(old('dags') as $index => $dag)
                                            @if($index > 0)
                                                <div class="d-flex flex-wrap align-items-center gap-2 dag-row p-3 rounded-3" style="background: rgba(255,255,255,0.7); backdrop-filter: blur(5px); border: 1.5px solid rgba(102,126,234,0.15);">
                                                    <input type="text" class="form-control rounded-3 flex-1" name="dags[{{ $index }}][dag]" placeholder="দাগ নম্বর" value="{{ $dag['dag'] ?? '' }}" style="{{ $inputStyle }}" onfocus="{{ $focusOn }}" onblur="{{ $focusOff }}">
                                                    <input type="text" class="form-control rounded-3 flex-1" name="dags[{{ $index }}][type]" placeholder="খতিয়ান শ্রেণি" value="{{ $dag['type'] ?? '' }}" style="{{ $inputStyle }}" onfocus="{{ $focusOn }}" onblur="{{ $focusOff }}">
                                                    <input type="text" class="form-control rounded-3 flex-1" name="dags[{{ $index }}][amount]" placeholder="খতিয়ান পরিমাণ (EN)" value="{{ $dag['amount'] ?? '' }}" style="{{ $inputStyle }}" onfocus="{{ $focusOn }}" onblur="{{ $focusOff }}">
                                                    <button type="button" class="remove-btn btn btn-sm fw-bold text-white rounded-3" style="background: rgba(239,68,68,0.8); border: none; padding: 6px 14px;">×</button>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Total in Words -->
                        <div>
                            <label class="form-label fw-semibold mb-2 d-block" style="{{ $labelStyle }}">সর্বমোট (কথায়)</label>
                            <input type="text" name="total_in_words" value="{{ old('total_in_words') }}" required class="form-control rounded-3" style="{{ $inputStyle }}" onfocus="{{ $focusOn }}" onblur="{{ $focusOff }}">
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex align-items-center gap-3 mt-3 pt-3" style="border-top: 1.5px solid rgba(102,126,234,0.12);">
                            <a href="{{ route('user.dashboard.dakhila.index') }}"
                               class="btn fw-semibold px-4 py-2 rounded-4"
                               style="background: rgba(255,255,255,0.85); backdrop-filter: blur(10px); color: #667eea; border: 1.5px solid rgba(102,126,234,0.25); box-shadow: 0 4px 15px rgba(102,126,234,0.1); transition: all 0.3s ease;"
                               onmouseover="this.style.background='rgba(102,126,234,0.08)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(102,126,234,0.2)';"
                               onmouseout="this.style.background='rgba(255,255,255,0.85)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(102,126,234,0.1)';">⬅️ ফিরে যান</a>
                            <button type="submit"
                                    class="btn btn-lg text-white fw-bold px-5 py-3 position-relative overflow-hidden ms-auto"
                                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #e83e8c 100%); border: none; border-radius: 16px; font-size: 1.05rem; box-shadow: 0 8px 30px rgba(102,126,234,0.35); transition: all 0.3s ease;"
                                    onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 40px rgba(102,126,234,0.5)';"
                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 30px rgba(102,126,234,0.35)';">
                                <span style="position: absolute; top: 0; left: -100%; width: 60%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent); transform: skewX(-20deg); animation: btnShine 2.5s ease-in-out infinite; pointer-events: none;"></span>
                                <span style="position: relative; z-index: 1;" id="submitText">💾 সংরক্ষণ করুন</span>
                                <svg class="ms-2 d-none" id="submitSpinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="width:20px;height:20px;animation: spin 1s linear infinite; position: relative; z-index: 1;">
                                    <circle style="opacity:0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path style="opacity:0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        @keyframes btnShine {
            0% { left: -100%; }
            40%, 100% { left: 200%; }
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</x-app-layout>

@push('styles')
<style>
    .animate-spin {
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>
@endpush

<script>
    // Make these variables and functions globally accessible
    var ownerIndex = {{ old('owners') ? count(old('owners')) : 1 }}; // Adjust based on old values
    var dagIndex = {{ old('dags') ? count(old('dags')) : 1 }};   // Adjust based on old values

    // Glossy input styles for dynamic rows
    var glossyInputStyle = 'border: 1.5px solid rgba(102,126,234,0.2); background: rgba(255,255,255,0.8); backdrop-filter: blur(5px); box-shadow: 0 2px 8px rgba(0,0,0,0.04) inset; padding: 12px 16px; transition: all 0.3s ease;';
    var glossyFocusOn = "this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102,126,234,0.15), 0 2px 8px rgba(0,0,0,0.04) inset';";
    var glossyFocusOff = "this.style.borderColor='rgba(102,126,234,0.2)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.04) inset';";

    // Function to add an owner row
    function addOwnerRow(name = '', share = '') {
        const ownerSection = document.getElementById('ownerSection');
        const div = document.createElement('div');
        div.className = 'd-flex flex-wrap align-items-center gap-2 owner-row p-3 rounded-3 mb-2';
        div.style.cssText = 'background: rgba(255,255,255,0.7); backdrop-filter: blur(5px); border: 1.5px solid rgba(102,126,234,0.15);';
        div.innerHTML = `
            <input type="text" class="form-control rounded-3 flex-1" name="owners[${ownerIndex}][name]" placeholder="মালিকের নাম" value="${name}" style="${glossyInputStyle}" onfocus="${glossyFocusOn}" onblur="${glossyFocusOff}">
            <input type="number" step="any" class="form-control rounded-3 flex-1" name="owners[${ownerIndex}][share]" placeholder="সম্পত্তির পরিমাণ (ইউজার)" value="${share}" style="${glossyInputStyle}" onfocus="${glossyFocusOn}" onblur="${glossyFocusOff}">
            <button type="button" class="remove-btn btn btn-sm fw-bold text-white rounded-3" style="background: rgba(239,68,68,0.8); border: none; padding: 6px 14px;">×</button>
        `;
        ownerSection.appendChild(div);
        ownerIndex++;
    }

    // Function to add a dag row
    function addDagRow(dag = '', type = '', amount = '') {
        const dagSection = document.getElementById('dagSection');
        const div = document.createElement('div');
        div.className = 'd-flex flex-wrap align-items-center gap-2 dag-row p-3 rounded-3 mb-2';
        div.style.cssText = 'background: rgba(255,255,255,0.7); backdrop-filter: blur(5px); border: 1.5px solid rgba(102,126,234,0.15);';
        div.innerHTML = `
            <input type="text" class="form-control rounded-3 flex-1" name="dags[${dagIndex}][dag]" placeholder="দাগ নম্বর" value="${dag}" style="${glossyInputStyle}" onfocus="${glossyFocusOn}" onblur="${glossyFocusOff}">
            <input type="text" class="form-control rounded-3 flex-1" name="dags[${dagIndex}][type]" placeholder="খতিয়ান শ্রেণি" value="${type}" style="${glossyInputStyle}" onfocus="${glossyFocusOn}" onblur="${glossyFocusOff}">
            <input type="text" class="form-control rounded-3 flex-1" name="dags[${dagIndex}][amount]" placeholder="খতিয়ান পরিমাণ (EN)" value="${amount}" style="${glossyInputStyle}" onfocus="${glossyFocusOn}" onblur="${glossyFocusOff}">
            <button type="button" class="remove-btn btn btn-sm fw-bold text-white rounded-3" style="background: rgba(239,68,68,0.8); border: none; padding: 6px 14px;">×</button>
        `;
        dagSection.appendChild(div);
        dagIndex++;
    }

    // Handle remove button clicks
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-btn')) {
            const row = e.target.closest('.owner-row, .dag-row');
            if (row && !row.querySelector('input[name="owners[0][name]"], input[name="dags[0][dag]"]')) {
                row.remove();
            }
        }
    });

    // Function to set payment_year_en when year changes
    function setPaymentYear() {
        const yearSelect = document.getElementById('year');
        const paymentYearEn = document.getElementById('payment_year_en');
        if (yearSelect && paymentYearEn && yearSelect.value) {
            paymentYearEn.value = yearSelect.value;
            // Also set payment_year_bn to the same value
            const paymentYearBn = document.getElementById('payment_year_bn');
            if (paymentYearBn) {
                paymentYearBn.value = yearSelect.value;
            }
        }
    }

    // Listen for year selection changes
    document.addEventListener('DOMContentLoaded', function() {
        const yearSelect = document.getElementById('year');
        if (yearSelect) {
            yearSelect.addEventListener('change', setPaymentYear);
            // Initialize on page load if there's already a value
            if (yearSelect.value) {
                setPaymentYear();
            }
        }
    });

    // Handle form submission
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const submitButton = this.querySelector('button[type="submit"]');
                const submitSpinner = document.getElementById('submitSpinner');
                const submitText = document.getElementById('submitText');
                
                // Ensure payment_year_en is set before submitting
                setPaymentYear();
                
                submitButton.disabled = true;
                if (submitSpinner) submitSpinner.classList.remove('d-none');
                if (submitText) submitText.textContent = 'প্রক্রিয়াকরণ...';
            });
        }
    });
</script>