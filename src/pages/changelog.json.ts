import changelog from '../data/changelog.json';

// Feed pubblico del changelog: lo legge anche GestHall Suite per il riquadro «Novità» del menu profilo.
export function GET() {
  const releases = changelog.releases.map((r) => ({
    ...r,
    url: `https://gesthallsuite.it/changelog#v${r.version}`,
  }));
  return new Response(JSON.stringify({ updated: changelog.updated, latest: releases[0]?.version ?? null, releases }), {
    headers: { 'Content-Type': 'application/json; charset=utf-8' },
  });
}
