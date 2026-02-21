export function evaluateResult(subjects, config) {
  const total = subjects.reduce((sum, s) => sum + Number(s.mark || 0), 0);
  const percentage = config.total_marks > 0 ? Number(((total / config.total_marks) * 100).toFixed(2)) : 0;
  const pass = subjects.every((s) => Number(s.mark) >= config.pass_mark);

  let grade = 'N/A';
  const grades = typeof config.grade_system === 'string' ? JSON.parse(config.grade_system || '[]') : config.grade_system;
  for (const g of grades) {
    if (percentage >= g.min && percentage <= g.max) {
      grade = g.grade;
      break;
    }
  }

  return {
    total,
    percentage,
    grade,
    status: pass ? 'PASS' : 'FAIL'
  };
}
