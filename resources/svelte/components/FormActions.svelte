<script>
	import { Button } from '$lib/components/ui/button/index.js';
	import { shouldSkipFormConfirmation, submitConfirmedForm } from '$lib/confirmable-form.js';

	let {
		formId = '',
		submitLabel = 'Simpan',
		submitIcon = 'fas fa-save',
		cancelAction = null,
		dangerAction = null,
		csrfToken = '',
	} = $props();

	const confirmSubmission = async (event, action) => {
		const form = event.currentTarget;

		if (shouldSkipFormConfirmation(form)) {
			return;
		}

		if (!action?.confirm) {
			return;
		}

		event.preventDefault();

		const text = action.confirmText || `Lanjutkan tindakan untuk ${action.confirm}?`;

		if (window.Swal) {
			const result = await window.Swal.fire({
				title: action.confirmTitle || 'Konfirmasi',
				text,
				icon: action.confirmIcon || 'warning',
				showCancelButton: true,
				confirmButtonText: action.confirmButtonText || 'Lanjutkan',
				cancelButtonText: 'Batal',
			});

			if (result.isConfirmed) {
				submitConfirmedForm(form);
			}

			return;
		}

		if (window.confirm(text)) {
			submitConfirmedForm(form);
		}
	};

	const cancelVariant = (item) => item?.variant || 'secondary';
	const dangerVariant = (item) => item?.variant || 'destructive';
</script>

<div class:split={Boolean(dangerAction)} class="form-actions">
	<div class="form-actions-main">
		<Button type="submit" form={formId} variant="default">
			{#if submitIcon}
				<i class={submitIcon}></i>
			{/if}
			<span>{submitLabel}</span>
		</Button>

		{#if cancelAction}
			<Button href={cancelAction.href} variant={cancelVariant(cancelAction)}>
				{#if cancelAction.icon}
					<i class={cancelAction.icon}></i>
				{/if}
				<span>{cancelAction.label}</span>
			</Button>
		{/if}
	</div>

	{#if dangerAction}
		<form method="POST" action={dangerAction.action} onsubmit={(event) => confirmSubmission(event, dangerAction)}>
			<input type="hidden" name="_token" value={csrfToken} />
			{#if dangerAction.method}
				<input type="hidden" name="_method" value={dangerAction.method} />
			{/if}

			<Button type="submit" variant={dangerVariant(dangerAction)}>
				{#if dangerAction.icon}
					<i class={dangerAction.icon}></i>
				{/if}
				<span>{dangerAction.label}</span>
			</Button>
		</form>
	{/if}
</div>

<style>
	.form-actions {
		display: flex;
		flex-wrap: wrap;
		gap: 0.75rem;
		margin-top: 1.5rem;
	}

	.form-actions.split {
		justify-content: space-between;
		align-items: center;
	}

	.form-actions-main {
		display: flex;
		flex-wrap: wrap;
		gap: 0.75rem;
	}

	form {
		margin: 0;
	}

	@media (max-width: 767px) {
		.form-actions.split {
			align-items: stretch;
		}
	}
</style>
