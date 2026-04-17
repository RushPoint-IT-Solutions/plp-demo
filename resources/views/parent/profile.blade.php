@extends('layouts.parent')

@section('title', 'PLP - Parent Profile')
@section('page-title', 'PARENT PROFILE')

@section('content')
@php
	$parentName = !empty(optional($user)->name) ? optional($user)->name : 'Emelie Tamayo Garma';
	$nameParts = preg_split('/\s+/', trim($parentName));
	$firstName = isset($nameParts[0]) ? $nameParts[0] : 'Emelie';
	$middleName = isset($nameParts[1]) ? $nameParts[1] : 'Tamayo';
	$lastName = isset($nameParts[2]) ? implode(' ', array_slice($nameParts, 2)) : 'Garma';
	$suffix = 'MS.';
	$parentEmail = !empty(optional($user)->email) ? optional($user)->email : 'test@gmail.com';
@endphp

<div class="parent-profile-snapshot-page">
	<div class="parent-profile-form-grid">
		<div class="parent-form-field parent-form-field-suffix">
			<label>SUFFIX</label>
			<input type="text" value="{{ $suffix }}" readonly>
		</div>
		<div class="parent-form-field">
			<label>FIRST NAME</label>
			<input type="text" value="{{ $firstName }}" readonly>
		</div>
		<div class="parent-form-field">
			<label>MIDDLE NAME</label>
			<input type="text" value="{{ $middleName }}" readonly>
		</div>
		<div class="parent-form-field">
			<label>LAST NAME</label>
			<input type="text" value="{{ $lastName }}" readonly>
		</div>
	</div>

	<div class="parent-profile-email-row">
		<div class="parent-form-field parent-form-field-email">
			<label>EMAIL</label>
			<input type="email" value="{{ $parentEmail }}" readonly>
		</div>
		<button type="button" class="parent-save-email-btn">Save Email</button>
	</div>

	<div class="parent-linked-children-box">
		<div class="parent-linked-children-head">
			<button type="button" class="req-btn-save parent-link-child-btn" data-bs-toggle="modal" data-bs-target="#parentLinkChildModal">
				Link another child
			</button>
		</div>

		<div class="grades-scroll">
			<table class="sched-table parent-child-table">
				<thead>
					<tr>
						<th class="sched-th">#</th>
						<th class="sched-th">Student No.</th>
						<th class="sched-th">Full Name</th>
						<th class="sched-th">Birthdate</th>
					</tr>
				</thead>
				<tbody>
					@forelse($children as $index => $child)
						<tr>
							<td class="sched-td">{{ $index + 1 }}</td>
							<td class="sched-td">{{ $child['student_no'] ?: 'N/A' }}</td>
							<td class="sched-td">{{ $child['name'] ?: 'N/A' }}</td>
							<td class="sched-td">{{ !empty($child['birthdate']) ? $child['birthdate'] : '-' }}</td>
						</tr>
					@empty
						<tr>
							<td class="sched-td" colspan="4">No linked children yet.</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>
	</div>
</div>

<div class="modal fade" id="parentLinkChildModal" tabindex="-1" aria-labelledby="parentLinkChildModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content parent-link-modal">
			<div class="modal-header parent-link-modal-head">
				<h5 class="modal-title" id="parentLinkChildModalLabel">Link Another Child</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body parent-link-modal-body">
				<p class="parent-empty-note">This is a frontend preview only. Backend linking will be added later.</p>
				<div class="mb-3">
					<label class="req-modal-label" for="childStudentNo">Student Number</label>
					<input id="childStudentNo" type="text" class="req-modal-input" placeholder="Enter student number">
				</div>
				<div class="mb-0">
					<label class="req-modal-label" for="childBirthDate">Birthdate</label>
					<input id="childBirthDate" type="date" class="req-modal-input">
				</div>
			</div>
			<div class="modal-footer parent-link-modal-actions">
				<button type="button" class="req-btn-cancel" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="req-btn-save" data-bs-dismiss="modal">Link Child</button>
			</div>
		</div>
	</div>
</div>
@endsection
