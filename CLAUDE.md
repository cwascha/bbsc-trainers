# BBSC Trainer Dashboard — Claude Instructions

## Git Workflow

**Always use feature branches.** Before starting any new feature or bug fix:

1. Pull latest main: `git checkout main && git pull origin main`
2. Create a branch: `git checkout -b feature/<short-slug>`
   - Use kebab-case slugs, e.g. `feature/past-sessions-view`, `feature/sms-to-day`
3. Commit all work to that branch
4. When the user asks for a PR (or at natural end of session), push the branch and open a PR targeting `main` via `gh pr create`

Do **not** commit directly to `main`.
