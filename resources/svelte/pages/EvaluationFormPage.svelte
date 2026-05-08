<script>
  import * as Card from "$lib/components/ui/card/index.js";
  import { Label } from "$lib/components/ui/label/index.js";
  import { Textarea } from "$lib/components/ui/textarea/index.js";
  import FormActions from "../components/FormActions.svelte";
  import PageHeader from "../components/PageHeader.svelte";

  let {
    title = "Form Evaluasi",
    description = "",
    staff = null,
    evaluatorType = "",
    periodLabel = "",
    gradeLegend = [],
    criteria = [],
    form = {
      action: "#",
      method: "POST",
      spoofMethod: null,
      csrfToken: "",
      hidden: [],
    },
    values = {},
    errors = {},
    cancelAction = null,
  } = $props();

  const formId = "evaluation-form";

  const scoreCopy = {
    1: "Perlu bimbingan",
    2: "Cukup",
    3: "Baik",
    4: "Sangat baik",
    5: "Istimewa",
  };

  let scoreValues = $state({});
  let scoreValuesInitialized = $state(false);
  const fallbackAvatar = (name = "User") =>
    `https://ui-avatars.com/api/?name=${encodeURIComponent(name || "User")}&background=251d39&color=f5c518&bold=true`;

  $effect(() => {
    if (!scoreValuesInitialized) {
      scoreValues = Object.fromEntries(
        criteria.map((criterion) => [
          criterion.key,
          Number(values[criterion.key] ?? 3),
        ]),
      );
      scoreValuesInitialized = true;
    }
  });

  const setScore = (key, value) => {
    scoreValues = {
      ...scoreValues,
      [key]: value,
    };
  };

  const handleImageError = (event) => {
    const nextSrc = fallbackAvatar(event.currentTarget.alt || "User");

    if (event.currentTarget.src === nextSrc) {
      return;
    }

    event.currentTarget.src = nextSrc;
  };

  const averageScore = $derived.by(() =>
    criteria.length
      ? (
          criteria.reduce(
            (total, criterion) =>
              total + Number(scoreValues[criterion.key] ?? 0),
            0,
          ) / criteria.length
        ).toFixed(2)
      : "0.00",
  );

  const errorEntries = $derived.by(() => Object.entries(errors || {}));
</script>

<div class="row justify-center">
  <div class="col-lg-10 col-12">
    {#if staff}
      <section class="rounded-[10px] border border-border bg-card px-5 py-5">
        <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_14rem] lg:items-end">
          <div class="flex items-start gap-4">
            <img
              src={staff.avatar || fallbackAvatar(staff.name)}
              alt={staff.name}
              class="h-16 w-16 rounded-full border border-border object-cover"
              width="64"
              height="64"
              loading="lazy"
              decoding="async"
              onerror={handleImageError}
            />
            <div class="min-w-0">
              <div class="text-sm font-medium text-brand-primary">
                {evaluatorType}
              </div>
              <h2 class="mt-2 text-2xl font-semibold text-foreground">
                {staff.name}
              </h2>
              <p class="mt-2 text-sm leading-7 text-muted-foreground">
                {staff.department} • {staff.email}
              </p>
            </div>
          </div>

          <div
            class="grid gap-3 rounded-[10px] border border-border bg-background px-4 py-4 text-sm"
          >
            <div>
              <div class="text-muted-foreground">Periode</div>
              <div class="mt-1 font-semibold text-foreground">
                {periodLabel}
              </div>
            </div>
            <div>
              <div class="text-muted-foreground">Rata-rata sementara</div>
              <div class="mt-1 font-semibold text-foreground">
                {averageScore}
              </div>
            </div>
          </div>
        </div>
      </section>
    {/if}

    <Card.Root
      class="mt-4 rounded-[10px] border border-border bg-card shadow-none"
    >
      <Card.Header class="border-b border-border/70 pb-4">
        <PageHeader {title} {description} icon="fas fa-star" />
      </Card.Header>

      <Card.Content class="pt-5">
        {#if errorEntries.length}
          <div
            class="rounded-[10px] border border-[color:color-mix(in_srgb,var(--signal-danger)_32%,transparent)] bg-[color:color-mix(in_srgb,var(--signal-danger)_10%,transparent)] px-4 py-3 text-sm text-[var(--signal-danger)]"
          >
            <strong>Periksa kembali input evaluasi.</strong>
            <ul class="mt-2 grid gap-1 pl-5">
              {#each errorEntries as entry, index (entry[0] || index)}
                <li>{entry[1]}</li>
              {/each}
            </ul>
          </div>
        {/if}

        {#if gradeLegend.length}
          <div
            class="mt-4 flex flex-wrap gap-2 rounded-[10px] border border-border bg-background px-4 py-4 text-sm"
          >
            {#each gradeLegend as grade, index (grade.label || index)}
              <span
                class="rounded-[8px] px-2.5 py-1"
                style={`background:color-mix(in srgb, ${grade.color} 16%, transparent); color:${grade.color};`}
              >
                {grade.range} · {grade.label}
              </span>
            {/each}
          </div>
        {/if}

        <form
          id={formId}
          method={form.method || "POST"}
          action={form.action}
          class="mt-5 grid gap-5"
        >
          <input type="hidden" name="_token" value={form.csrfToken} />
          {#if form.spoofMethod}
            <input type="hidden" name="_method" value={form.spoofMethod} />
          {/if}
          {#each form.hidden || [] as item, index (item.name || index)}
            <input type="hidden" name={item.name} value={item.value} />
          {/each}

          <div class="grid gap-4">
            {#each criteria as criterion, index (criterion.key || index)}
              <fieldset
                class="rounded-[10px] border border-border bg-background px-4 py-4"
              >
                <legend class="px-1 text-sm font-semibold text-brand-primary"
                  >Kriteria {index + 1}</legend
                >
                <div
                  class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,25rem)] lg:items-start"
                >
                  <div>
                    <h3 class="text-lg font-semibold text-foreground">
                      {criterion.label}
                    </h3>
                    <p class="mt-2 text-sm leading-7 text-muted-foreground">
                      {criterion.description}
                    </p>
                    <div class="mt-4 text-sm text-muted-foreground">
                      Nilai dipilih: <strong class="text-foreground"
                        >{scoreValues[criterion.key] ?? 3}</strong
                      >
                    </div>
                  </div>

                  <div class="grid gap-2 sm:grid-cols-5">
                    {#each [1, 2, 3, 4, 5] as score (score)}
                      <div class="evaluation-score-option">
                        <input
                          id={`${criterion.key}-${score}`}
                          class="sr-only"
                          type="radio"
                          name={criterion.key}
                          value={score}
                          checked={Number(scoreValues[criterion.key] ?? 3) ===
                            score}
                          required
                          onchange={() => setScore(criterion.key, score)}
                        />
                        <label
                          for={`${criterion.key}-${score}`}
                          class={`block rounded-[10px] border px-3 py-3 text-center transition-colors ${Number(scoreValues[criterion.key] ?? 3) === score ? "border-brand-primary bg-brand-light/20 text-foreground" : "border-border bg-card text-muted-foreground hover:bg-muted"}`}
                        >
                          <strong class="block text-lg text-foreground"
                            >{score}</strong
                          >
                          <span class="mt-1 block text-xs"
                            >{scoreCopy[score]}</span
                          >
                        </label>
                      </div>
                    {/each}
                  </div>
                </div>

                {#if errors[criterion.key]}
                  <div
                    class="mt-3 text-sm text-[var(--signal-danger)]"
                    role="alert"
                  >
                    {errors[criterion.key]}
                  </div>
                {/if}
              </fieldset>
            {/each}
          </div>

          <div
            class="rounded-[10px] border border-border bg-background px-4 py-4"
          >
            <Label for="evaluation-notes">Catatan / feedback</Label>
            <Textarea
              id="evaluation-notes"
              name="notes"
              class="mt-2"
              aria-invalid={Boolean(errors.notes)}
              rows="4"
              placeholder="Tulis feedback..."
              value={values.notes || ""}
            />
            {#if errors.notes}
              <div
                class="mt-2 text-sm text-[var(--signal-danger)]"
                role="alert"
              >
                {errors.notes}
              </div>
            {/if}
          </div>
        </form>

        <div class="mt-5 border-t border-border pt-5">
          <FormActions
            {formId}
            submitLabel="Simpan evaluasi"
            submitIcon="fas fa-save"
            {cancelAction}
          />
        </div>
      </Card.Content>
    </Card.Root>
  </div>
</div>
