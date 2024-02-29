import React, { useState,useEffect }  from 'react';
import axios from 'axios';

function Region_all() {

interface Region_all_Type{
  
  
}


  const [constellations, setConstellations] = useState<Region_all_Type[]>([]);

  useEffect(() => {
    regions();
  }, []);

  
  const regions = async () => {
    try { 
        const response = await axios.get("https://cdn.jsdelivr.net/gh/IDimasaI/regions_and_IdConstellation@v2/regions.json");
        const result=response.data;
        setConstellations(result)
    } catch (error) {
      console.error(error);
    }
  };
  function replaceSpace(text:string){
    return text.replace(/\s/g, "_");
  }
  return (
    <div>
      <h1>Просмотр всех регионов</h1>
      <ol>
        {!constellations.length && <li>Loading</li>}
        {constellations.map((constellation,index) => (
          <div key={index}>
          {Object.keys(constellation).map((object:string) => (
              <li key={index}>
                <a href={'/Regions/'+replaceSpace(object)} target="_blank" rel="noopener noreferrer">
                  {object}
                </a>
             </li>
            )
          )}
          </div>
        ))}
      </ol>
    </div>
  );
}



function Constellation_system() {
  interface DataType {
    name: string;
    id: number;
    

    systems:any;
    systems_count:number;
    system_id:number;
    constellations:number[];

    total_kills_nps:number;
    total_kills_ship:number;
    total_kills_pod:number;
    

    matchedSystems: Record<string, number>;
    npc_kills:number;
    ship_kills:number;
    pod_kills:number;

  }
  interface RegionDataType {
    id: number;
    name:string;
  }
  const [RegionData, setRegion] = useState<string>();
  const [killsData, setKillsData] = useState<DataType[]>([]);
  const [Count, setCount] = useState<number>(0);
  const [constellations, setConstellations] = useState<DataType[]>([]);
  const [systemsData, setSystemsData] = useState<DataType[]>([]);
  const [typeSystem, setTypeSystem]=useState<number>(0);
    useEffect(() => {
      killData();
    }, []);
 
  useEffect(() => {
    if (Count === 1) {
      regions();
    }
  }, [Count]);

  const killData = async () => {
    try {
      const response = await axios.get(`https://esi.evetech.net/latest/universe/system_kills`);
      const data = response.data;
      setKillsData(data);
      setCount(1);
      console.log(data);
    } catch (error) {
      console.log(error);
    }
  };

  const regions = async () => {
    try {
      const info = document.getElementById('info') as HTMLInputElement;
      const response = await axios.get("https://cdn.jsdelivr.net/gh/IDimasaI/regions_and_IdConstellation@v2/regions.json");
  
      const results = response.data.filter((item: { [key: string]: DataType }) => Object.keys(item)[0] === info.value);
  
      if (results.length > 0) {
        results.forEach((result: { [key: string]: DataType }) => {
          const regionArray=result[Object.keys(result)[0]]
          setTypeSystem(regionArray.id);
          const constellationsID = regionArray.constellations; 
          const nameRegion=regionArray.name
          console.log('регион'); 
            get_constellations_and_systems_info(constellationsID);
            setRegion(nameRegion);
        });
      } else {
        console.log("Регион не найден");
      }
  
    } catch (error) {
      console.error(error);
    }
  }

  const get_constellations_and_systems_info = async (constellations:number[]) => {
    try {
      const chunkSize = 10;
      const chunks = [];

      for (let i = 0; i < constellations.length; i += chunkSize) {
        const chunk = constellations.slice(i, i + chunkSize);
        chunks.push(chunk);
      }

      const results = [];
      for (let i = 0; i < chunks.length; i++) {
        const response = await axios.post("https://esi.evetech.net/latest/universe/names/?datasource=tranquility&language=ru", '[' + chunks[i].join(",") + ']');
        const constellationsData = response.data;

        for (let j = 0; j < constellationsData.length; j++) {
          const constellation = constellationsData[j];
          const constellationID = constellation.id;

          const response2 = await axios.get(`https://esi.evetech.net/latest/universe/constellations/${constellationID}`);
          const systemsData = response2.data;

          // Сопоставление ID систем в созвездии с килами
          const matchedSystems:Record<string, number> = {};
          
          for (const key in killsData) {
            const systemId = killsData[key].system_id;
            if (systemsData.systems.includes(systemId)) {
              matchedSystems[key] = systemId;
            }
          }

          // Получение количества килов в созвездии
          const killsDataObj:Record<number, DataType> = {};
          for (const killData of killsData) {
            killsDataObj[killData.system_id] = killData;
          }

          let totalKills_nps = 0;
          let totalKills_ship = 0;
          let totalKills_pod = 0;
          for (const systemId of systemsData.systems) {
            if (killsDataObj[systemId]) {
              totalKills_nps += killsDataObj[systemId].npc_kills;
              totalKills_ship += killsDataObj[systemId].ship_kills;
              totalKills_pod += killsDataObj[systemId].pod_kills;
            }
          }

          const result = {
            ...constellation,
            systems: systemsData.systems,
            systems_count: systemsData.systems.length,
            total_kills_nps: totalKills_nps,
            total_kills_ship: totalKills_ship,
            total_kills_pod: totalKills_pod,
          };

          results.push(result);
        }
      }

      setConstellations(results);
      console.log(results);
      setCount(0);
      
    } catch (error) {
      console.error(error);
    }
  };

  const Solars_systems = async (id:number[],typeSystem:number) => {
    try {
      console.log(typeSystem);
      const response = await axios.post("https://esi.evetech.net/latest/universe/names/?datasource=tranquility&language=ru", id);
      const results = response.data;
      console.log(results);
      return results;
    } catch (error) {
      console.error(error);
      return null;
    }
  };
  useEffect(() => {
    const fetchData = async () => {
      const data = await Promise.all(constellations.map(async (constellation) => {
        const systems = await Solars_systems(constellation.systems,typeSystem);
        const modifiedSystems = systems.map((system: { id: number; }) => {
          const systemId = system.id;
          const killsDataObj = killsData.find((killData) => killData.system_id === systemId);
  
          return {
            ...system,
            npc_kills: killsDataObj ? killsDataObj.npc_kills : 0,
            ship_kills: killsDataObj ? killsDataObj.ship_kills : 0,
            pod_kills: killsDataObj ? killsDataObj.pod_kills : 0
          };
        });
  
        return {
          ...constellation,
          systems: modifiedSystems,
        };
      }));
      
      if(typeof data==='object'&&data.length>0){
        setSystemsData(data);
        console.log('результат')
        console.log(data)
      }
    };
    fetchData();
    
  }, [constellations]);

  function replaceSpace(text:string) {
    return text.replace(/\s/g, "_");
  }

  return (
    <div>
      <h2>Регион {RegionData}</h2>
      <h1>Созвездия</h1>
      <ul>
        {constellations.length === 0 && <li>Loading</li>}
        {systemsData.map((constellation, index) => (
          <div key={index}>
          <li >
            {index + 1 + ') '} Созвездие:{" "}
            <a href={"/constellation/" + replaceSpace(constellation.name)} target="_blank" rel="noopener noreferrer">
              {constellation.name}
            </a>{" "}
            <ul>
              {constellation.systems.map((system: DataType) => (
                <div key={system.id}>
                  <li>
                    <a href={'/System/' + replaceSpace(system.name)} target="_blank" rel="noopener noreferrer">{system.name}</a>
                  </li>
                    <span>Убито npc: {system.npc_kills}, Убито кораблей {system.ship_kills}, Капсул {system.pod_kills} </span>
                </div>
              ))}
            </ul>
            Число систем: {constellation.systems_count},
            Количество килов npc: {constellation.total_kills_nps},
            Убито кораблей: {constellation.total_kills_ship},
            капсул: {constellation.total_kills_pod}
          </li>
          <hr />
          </div>
        ))}
      </ul>
    </div>
  );
}

export  {Constellation_system,Region_all};
